<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\ReliefAssistanceOperation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReliefAssistanceOperationController extends Controller
{
    public function index(Request $request): View
    {
        $query = ReliefAssistanceOperation::query()
            ->with('creator')
            ->withCount('beneficiaries')
            ->latest();

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('operation_code', 'like', '%' . $search . '%')
                    ->orWhere('operation_name', 'like', '%' . $search . '%')
                    ->orWhere('assistance_type', 'like', '%' . $search . '%')
                    ->orWhere('source_name', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $operations = $query->paginate(10)->withQueryString();

        $totalOperations = ReliefAssistanceOperation::count();
        $verificationOperations = ReliefAssistanceOperation::where('status', 'verification')->count();
        $readyOperations = ReliefAssistanceOperation::where('status', 'ready')->count();
        $completedOperations = ReliefAssistanceOperation::where('status', 'completed')->count();

        return view(
            'assistance-campaigns.index',
            compact(
                'operations',
                'totalOperations',
                'verificationOperations',
                'readyOperations',
                'completedOperations'
            )
        );
    }

    public function create(): View
    {
        return view('assistance-campaigns.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'operation_name' => ['required', 'string', 'max:255'],
            'assistance_type' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'source_name' => ['required', 'string', 'max:255'],
            'source_reference' => ['nullable', 'string', 'max:255'],
            'assistance_received_date' => ['required', 'date'],
            'quantity_received' => ['required', 'integer', 'min:0'],
            'quantity_unit' => ['required', 'string', 'max:50'],
        ]);

        do {
            $operationCode =
                'RA-' .
                now()->format('Ymd') .
                '-' .
                strtoupper(Str::random(6));
        } while (
            ReliefAssistanceOperation::where('operation_code', $operationCode)->exists()
        );

        $operation = ReliefAssistanceOperation::create([
            'operation_code' => $operationCode,
            'operation_name' => $validated['operation_name'],
            'description' => $validated['description'] ?? null,
            'assistance_type' => $validated['assistance_type'],
            'assistance_description' => null,
            'source_name' => $validated['source_name'],
            'source_reference' => $validated['source_reference'] ?? null,
            'assistance_received_date' => $validated['assistance_received_date'],
            'quantity_received' => $validated['quantity_received'],
            'quantity_unit' => $validated['quantity_unit'],
            'quantity_allocated' => 0,
            'allocation_basis' => null,
            'allocation_notes' => null,
            'distribution_date' => null,
            'distribution_time' => null,
            'distribution_location' => null,
            'status' => 'draft',
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()
            ->route('relief-vouchers.index')
            ->with(
                'success',
                'Relief assistance operation created successfully. Operation Code: '
                . $operation->operation_code
            );
    }

    public function show(ReliefAssistanceOperation $operation): View
    {
        $operation->load([
            'creator',
            'updater',
            'beneficiaries.household.householdHead',
            'vouchers.household.householdHead',
        ]);

        return view('assistance-campaigns.show', compact('operation'));
    }

    public function edit(ReliefAssistanceOperation $operation): View
    {
        return view('assistance-campaigns.edit', compact('operation'));
    }

    public function update(
        Request $request,
        ReliefAssistanceOperation $operation
    ): RedirectResponse {
        if ($operation->isCompleted() || $operation->isCancelled()) {
            return back()->with(
                'error',
                'Completed or cancelled relief assistance operations cannot be edited.'
            );
        }

        $validated = $request->validate([
            'operation_name' => ['required', 'string', 'max:255'],
            'assistance_type' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'source_name' => ['required', 'string', 'max:255'],
            'source_reference' => ['nullable', 'string', 'max:255'],
            'assistance_received_date' => ['required', 'date'],
            'quantity_received' => ['required', 'integer', 'min:0'],
            'quantity_unit' => ['required', 'string', 'max:50'],
            'distribution_date' => ['nullable', 'date'],
            'distribution_time' => ['nullable', 'date_format:H:i'],
            'distribution_location' => ['nullable', 'string', 'max:255'],
            'status' => [
                'required',
                'in:draft,verification,ready,allocated,distribution,completed,cancelled',
            ],
            'allocation_basis' => ['nullable', 'string', 'max:255'],
            'allocation_notes' => ['nullable', 'string'],
        ]);

        if (
            !empty($validated['distribution_date'])
            && $validated['distribution_date'] < $validated['assistance_received_date']
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'distribution_date' =>
                        'The distribution date cannot be earlier than the assistance received date.',
                ]);
        }

        if (
            $operation->quantity_allocated !== null
            && $validated['quantity_received'] < $operation->quantity_allocated
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'quantity_received' =>
                        'Quantity received cannot be lower than the quantity already allocated.',
                ]);
        }

        $operation->update([
            'operation_name' => $validated['operation_name'],
            'description' => $validated['description'] ?? null,
            'assistance_type' => $validated['assistance_type'],
            'source_name' => $validated['source_name'],
            'source_reference' => $validated['source_reference'] ?? null,
            'assistance_received_date' => $validated['assistance_received_date'],
            'quantity_received' => $validated['quantity_received'],
            'quantity_unit' => $validated['quantity_unit'],
            'allocation_basis' => $validated['allocation_basis'] ?? null,
            'allocation_notes' => $validated['allocation_notes'] ?? null,
            'distribution_date' => $validated['distribution_date'] ?? null,
            'distribution_time' => $validated['distribution_time'] ?? null,
            'distribution_location' => $validated['distribution_location'] ?? null,
            'status' => $validated['status'],
            'updated_by' => Auth::id(),
        ]);

        return redirect()
            ->route('relief-vouchers.index')
            ->with('success', 'Relief assistance operation updated successfully.');
    }

    public function cancel(ReliefAssistanceOperation $operation): RedirectResponse
    {
        if ($operation->isCompleted()) {
            return back()->with(
                'error',
                'A completed relief assistance operation cannot be cancelled.'
            );
        }

        if ($operation->isCancelled()) {
            return back()->with(
                'error',
                'This relief assistance operation is already cancelled.'
            );
        }

        $operation->update([
            'status' => 'cancelled',
            'updated_by' => Auth::id(),
        ]);

        return redirect()
            ->route('relief-vouchers.index')
            ->with('success', 'Relief assistance operation cancelled successfully.');
    }

    public function masterlist(Request $request): View
    {
        $query = Household::query()
            ->with('householdHead')
            ->where('status', 'Active')
            ->orderBy('purok')
            ->orderBy('household_number');

        if ($request->filled('purok')) {
            $query->where('purok', $request->input('purok'));
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('household_number', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhereHas('householdHead', function ($headQuery) use ($search) {
                        $headQuery->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('middle_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%');
                    });
            });
        }

        $households = $query->get();

        return view('assistance-campaigns.masterlist', compact('households'));
    }

    public function printMasterlist(Request $request): View
    {
        $query = Household::query()
            ->with('householdHead')
            ->where('status', 'Active')
            ->orderBy('purok')
            ->orderBy('household_number');

        if ($request->filled('purok')) {
            $query->where('purok', $request->input('purok'));
        }

        $households = $query->get();

        return view('assistance-campaigns.masterlist-print', compact('households'));
    }
}
