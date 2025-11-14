<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanApplicationRequest;
use App\Models\LoanInstallment;
use App\Services\LoanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    public function __construct(private readonly LoanService $loanService)
    {
        $this->middleware('auth')->except(['guestCalculator', 'calculate']);
    }

    public function apply(LoanApplicationRequest $request): JsonResponse
    {
        $loan = $this->loanService->createLoan(Auth::user(), $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Loan application submitted successfully.',
            'loan' => $loan,
        ], 201);
    }

    public function list(Request $request): JsonResponse
    {
        $loans = $this->loanService->listLoans(Auth::user());

        return response()->json([
            'status' => true,
            'loans' => $loans,
        ]);
    }

    public function installments($loanId): JsonResponse
    {
        $installments = $this->loanService->listInstallments(Auth::user(), $loanId);

        return response()->json([
            'status' => true,
            'installments' => $installments,
        ]);
    }

    public function markInstallmentPaid(Request $request, LoanInstallment $installment): JsonResponse
    {
        if ($installment->loan->user_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'custom_penalty_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $this->loanService->markInstallmentPaid($installment, $data['custom_penalty_amount'] ?? null);

        return response()->json([
            'status' => true,
            'message' => 'Installment status updated.',
        ]);
    }

    public function calculate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'principal_amount' => ['required', 'numeric', 'min:1000'],
            'interest_rate' => ['required', 'numeric', 'min:1'],
            'tenure_months' => ['required', 'integer', 'min:1'],
            'custom_penalty_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        return response()->json([
            'status' => true,
            'results' => $this->loanService->calculateEmi($data),
        ]);
    }

    public function reminders(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'reminders' => $this->loanService->loanReminders(Auth::user()),
        ]);
    }

    public function guestCalculator()
    {
        return view('emi-calculator');
    }

    public function delete($loanId): JsonResponse
    {
        $loan = \App\Models\Loan::findOrFail($loanId);

        // Only admin can delete any loan, users can only delete their own loans
        if (!Auth::user()->isAdmin() && $loan->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $loan->delete();

        return response()->json([
            'status' => true,
            'message' => 'Loan deleted successfully.',
        ]);
    }

    public function deleteInstallment(LoanInstallment $installment): JsonResponse
    {
        // Only admin can delete EMIs
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Admin access required.',
            ], 403);
        }

        $this->loanService->deleteInstallment($installment);

        return response()->json([
            'status' => true,
            'message' => 'EMI deleted successfully. Remaining balance updated.',
        ]);
    }
}

