<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\User;
use App\Services\LoanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function __construct(private readonly LoanService $loanService)
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $users = User::withCount('loans')->latest()->get();
        $loans = Loan::with(['user', 'installments'])->latest()->get();

        return view('admin.dashboard', compact('users', 'loans'));
    }

    public function users(): JsonResponse
    {
        $users = User::withCount('loans')->latest()->get();

        return response()->json([
            'status' => true,
            'users' => $users,
        ]);
    }

    public function getUser($userId): JsonResponse
    {
        $user = User::withCount('loans')->findOrFail($userId);

        return response()->json([
            'status' => true,
            'user' => $user,
        ]);
    }

    public function createUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,user'],
            'aadhar_number' => ['required', 'digits:12', 'unique:users,aadhar_number'],
            'pan_number' => ['required', 'string', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'],
            'phone_number' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'age' => ['required', 'integer', 'min:18', 'max:120'],
            'address' => ['sometimes', 'nullable', 'string'],
            'area' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'state' => ['sometimes', 'nullable', 'string', 'max:255'],
            'zip_code' => ['sometimes', 'nullable', 'string', 'max:20'],
            'profession' => ['sometimes', 'nullable', 'string', 'max:255'],
            'education' => ['sometimes', 'nullable', 'string', 'max:255'],
            'additional_info' => ['sometimes', 'nullable', 'string'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'aadhar_number' => $validated['aadhar_number'],
            'pan_number' => $validated['pan_number'],
            'phone_number' => $validated['phone_number'],
            'age' => $validated['age'],
            'address' => $validated['address'] ?? null,
            'area' => $validated['area'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'zip_code' => $validated['zip_code'] ?? null,
            'profession' => $validated['profession'] ?? null,
            'education' => $validated['education'] ?? null,
            'additional_info' => $validated['additional_info'] ?? null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User created successfully.',
            'user' => $user,
        ], 201);
    }

    public function updateUser(Request $request, $userId): JsonResponse
    {
        $user = User::findOrFail($userId);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $userId],
            'password' => ['sometimes', 'nullable', 'string', 'min:6'],
            'role' => ['sometimes', 'in:admin,user'],
            'aadhar_number' => ['sometimes', 'digits:12', 'unique:users,aadhar_number,' . $userId],
            'pan_number' => ['sometimes', 'string', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'],
            'phone_number' => ['sometimes', 'string', 'regex:/^[0-9]{10}$/'],
            'age' => ['sometimes', 'integer', 'min:18', 'max:120'],
            'address' => ['sometimes', 'nullable', 'string'],
            'area' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'state' => ['sometimes', 'nullable', 'string', 'max:255'],
            'zip_code' => ['sometimes', 'nullable', 'string', 'max:20'],
            'profession' => ['sometimes', 'nullable', 'string', 'max:255'],
            'education' => ['sometimes', 'nullable', 'string', 'max:255'],
            'additional_info' => ['sometimes', 'nullable', 'string'],
        ]);

        if (isset($validated['password']) && !empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'User updated successfully.',
            'user' => $user->fresh(),
        ]);
    }

    public function deleteUser($userId): JsonResponse
    {
        $user = User::findOrFail($userId);

        // Prevent admin from deleting themselves
        if ($user->id === auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'You cannot delete your own account.',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'User deleted successfully.',
        ]);
    }

    public function allLoans(): JsonResponse
    {
        $loans = Loan::with(['user', 'installments'])->latest()->get();

        return response()->json([
            'status' => true,
            'loans' => $loans,
        ]);
    }

    public function deleteLoan($loanId): JsonResponse
    {
        $loan = Loan::findOrFail($loanId);
        $loan->delete();

        return response()->json([
            'status' => true,
            'message' => 'Loan deleted successfully.',
        ]);
    }

    public function getUserLoans($userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        $loans = $this->loanService->listLoans($user);

        return response()->json([
            'status' => true,
            'loans' => $loans,
        ]);
    }

    public function getUserReminders($userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        $reminders = $this->loanService->loanReminders($user);

        return response()->json([
            'status' => true,
            'reminders' => $reminders,
        ]);
    }

    public function getUserLoanSummary($userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        $summary = $this->loanService->summarizeLoans($user);

        return response()->json([
            'status' => true,
            'summary' => $summary,
        ]);
    }

    public function customizeEmiPayment(Request $request, $installmentId = null): JsonResponse
    {
        $validated = $request->validate([
            'custom_penalty_amount' => ['required', 'numeric', 'min:0'],
            'mark_as_paid' => ['sometimes', 'boolean'],
            'loan_id' => ['sometimes', 'integer', 'exists:loans,id'],
            'due_date' => ['sometimes', 'date'],
        ]);

        // Find installment by ID or by loan_id and due_date
        if ($installmentId) {
            $installment = \App\Models\LoanInstallment::findOrFail($installmentId);
        } elseif ($validated['loan_id'] && $validated['due_date']) {
            $installment = \App\Models\LoanInstallment::where('loan_id', $validated['loan_id'])
                ->whereDate('due_date', $validated['due_date'])
                ->firstOrFail();
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Installment ID or Loan ID with Due Date is required.',
            ], 400);
        }

        if ($request->boolean('mark_as_paid')) {
            $this->loanService->markInstallmentPaid($installment, $validated['custom_penalty_amount']);
            
            return response()->json([
                'status' => true,
                'message' => 'EMI marked as paid with custom penalty.',
            ]);
        } else {
            // Just update penalty without marking as paid
            $installment->update([
                'penalty_amount' => $validated['custom_penalty_amount'],
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Penalty amount updated successfully.',
            ]);
        }
    }
}
