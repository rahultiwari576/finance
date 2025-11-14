<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LoanService
{
    public function createLoan(User $user, array $data): Loan
    {
        return DB::transaction(function () use ($user, $data) {
            $calculation = $this->calculateEmi($data);

            $loan = $user->loans()->create([
                'principal_amount' => $calculation['principal_amount'],
                'interest_rate' => $calculation['interest_rate'],
                'tenure_months' => $calculation['tenure_months'],
                'emi_amount' => $calculation['emi_amount'],
                'total_repayment' => $calculation['total_repayment'],
                'status' => 'active',
                'next_due_date' => Carbon::parse($calculation['schedule'][0]['due_date']),
                'penalty_amount' => config('loan.default_penalty', 100),
                'custom_penalty_amount' => $data['custom_penalty_amount'] ?? null,
            ]);

            $this->createInstallments($loan, $calculation['schedule'], $data['custom_penalty_amount'] ?? null);

            return $loan->load('installments');
        });
    }

    public function calculateEmi(array $data): array
    {
        $principal = (float) $data['principal_amount'];
        $rate = (float) $data['interest_rate'] / 12 / 100;
        $tenure = (int) $data['tenure_months'];

        if ($rate === 0.0) {
            $emi = $principal / $tenure;
        } else {
            $emi = $principal * $rate * pow(1 + $rate, $tenure) / (pow(1 + $rate, $tenure) - 1);
        }

        $emi = round($emi, 2);
        $totalRepayment = round($emi * $tenure, 2);
        $schedule = [];
        $balance = $totalRepayment;
        $dueDate = Carbon::now()->addMonth();

        for ($i = 1; $i <= $tenure; $i++) {
            $schedule[] = [
                'installment_number' => $i,
                'due_date' => $dueDate->copy(),
                'amount' => $emi,
            ];
            $dueDate = $dueDate->copy()->addMonth();
            $balance -= $emi;
        }

        return [
            'principal_amount' => $principal,
            'interest_rate' => (float) $data['interest_rate'],
            'tenure_months' => $tenure,
            'emi_amount' => $emi,
            'total_repayment' => $totalRepayment,
            'schedule' => collect($schedule)->map(function ($item) {
                $item['due_date'] = $item['due_date']->format('Y-m-d');
                return $item;
            })->toArray(),
        ];
    }

    public function createInstallments(Loan $loan, array $schedule, ?float $customPenalty = null): void
    {
        foreach ($schedule as $item) {
            $loan->installments()->create([
                'due_date' => Carbon::parse($item['due_date']),
                'amount' => $item['amount'],
                'status' => 'pending',
                'penalty_amount' => $customPenalty ?? config('loan.default_penalty', 100),
            ]);
        }
    }

    public function listLoans(User $user): array
    {
        return $user->loans()
            ->with(['installments' => function ($query) {
                $query->orderBy('due_date');
            }])
            ->latest()
            ->get()
            ->map(function (Loan $loan) {
                return [
                    'id' => $loan->id,
                    'principal_amount' => (float) $loan->principal_amount,
                    'interest_rate' => (float) $loan->interest_rate,
                    'tenure_months' => (int) $loan->tenure_months,
                    'emi_amount' => (float) $loan->emi_amount,
                    'total_repayment' => (float) $loan->total_repayment,
                    'status' => $loan->status,
                    'next_due_date' => optional($loan->next_due_date)->format('Y-m-d'),
                    'penalty_amount' => (float) $loan->penalty_amount,
                    'custom_penalty_amount' => $loan->custom_penalty_amount ? (float) $loan->custom_penalty_amount : null,
                    'installments' => $loan->installments->map(function (LoanInstallment $installment) {
                        return [
                            'id' => $installment->id,
                            'due_date' => $installment->due_date->format('Y-m-d'),
                            'amount' => (float) $installment->amount,
                            'status' => $installment->status,
                            'penalty_amount' => (float) $installment->penalty_amount,
                            'paid_at' => optional($installment->paid_at)->toDateTimeString(),
                            'pay_url' => route('loans.installments.pay', $installment),
                        ];
                    })->toArray(),
                ];
            })
            ->toArray();
    }

    public function listInstallments(User $user, int $loanId)
    {
        $loan = $user->loans()->with('installments')->findOrFail($loanId);

        return $loan->installments->map(function (LoanInstallment $installment) {
            return [
                'id' => $installment->id,
                'due_date' => $installment->due_date->format('Y-m-d'),
                'amount' => (float) $installment->amount,
                'status' => $installment->status,
                'penalty_amount' => (float) $installment->penalty_amount,
                'paid_at' => optional($installment->paid_at)->toDateTimeString(),
                'pay_url' => route('loans.installments.pay', $installment),
            ];
        })->values();
    }

    public function markInstallmentPaid(LoanInstallment $installment, ?float $customPenalty = null): void
    {
        $penalty = $customPenalty !== null ? $customPenalty : $installment->penalty_amount;

        if ($installment->due_date->isPast() && $customPenalty === null) {
            $penalty = config('loan.default_penalty', 100);
        }

        $installment->update([
            'status' => 'paid',
            'paid_at' => Carbon::now(),
            'penalty_amount' => $penalty,
        ]);

        $loan = $installment->loan;

        $nextInstallment = $loan->installments()
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->first();

        $loan->update([
            'status' => $nextInstallment ? 'active' : 'completed',
            'next_due_date' => $nextInstallment?->due_date,
            'custom_penalty_amount' => $customPenalty ?? $loan->custom_penalty_amount,
        ]);
    }

    public function loanReminders(User $user): array
    {
        return $user->loans()
            ->with(['installments' => function ($query) {
                $query->whereIn('status', ['pending', 'overdue'])
                    ->orderBy('due_date');
            }])
            ->get()
            ->flatMap(function (Loan $loan) {
                return $loan->installments->map(function (LoanInstallment $installment) use ($loan) {
                    $status = $installment->status;

                    if ($status === 'pending' && $installment->due_date->isPast()) {
                        $installment->update(['status' => 'overdue']);
                        $status = 'overdue';
                    }

                    return [
                        'loan_id' => $loan->id,
                        'installment_id' => $installment->id,
                        'due_date' => $installment->due_date->format('Y-m-d'),
                        'amount' => $installment->amount,
                        'penalty_amount' => $installment->penalty_amount,
                        'status' => $status,
                    ];
                });
            })
            ->values()
            ->toArray();
    }

    public function summarizeLoans(User $user): array
    {
        $loans = $user->loans()->with('installments')->get();

        $totalPrincipal = $loans->sum('principal_amount');
        $totalRepayment = $loans->sum('total_repayment');
        $pendingInstallments = $loans->flatMap->installments->where('status', 'pending');
        $pendingAmount = $pendingInstallments->sum('amount');

        return [
            'total_principal' => $totalPrincipal,
            'total_repayment' => $totalRepayment,
            'pending_amount' => $pendingAmount,
            'pending_installments' => $pendingInstallments->count(),
        ];
    }

    public function deleteInstallment(LoanInstallment $installment): void
    {
        $loan = $installment->loan;
        
        // Delete the installment
        $installment->delete();

        // Recalculate remaining balance and update loan
        $remainingInstallments = $loan->installments()->orderBy('due_date')->get();
        
        if ($remainingInstallments->isEmpty()) {
            // No installments left, mark loan as completed
            $loan->update([
                'status' => 'completed',
                'next_due_date' => null,
            ]);
        } else {
            // Update next due date to the earliest pending installment
            $nextInstallment = $remainingInstallments
                ->where('status', 'pending')
                ->first();
            
            $loan->update([
                'status' => $nextInstallment ? 'active' : 'completed',
                'next_due_date' => $nextInstallment?->due_date,
            ]);
        }
    }
}

