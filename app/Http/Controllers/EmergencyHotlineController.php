<?php

namespace App\Http\Controllers;

use App\Models\EmergencyHotline;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmergencyHotlineController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SMART Emergency Hotline Directory
    |--------------------------------------------------------------------------
    |
    | This controller manages the official emergency contact directory
    | maintained by Barangay Poblacion.
    |
    | The system does NOT directly dispatch emergency responders.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Category Definitions
    |--------------------------------------------------------------------------
    */

    private function categories(): array
    {
        return [
            'Barangay',
            'Police',
            'Fire',
            'Medical / Ambulance',
            'Disaster / Emergency',
            'Rescue',
            'Other',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Status Definitions
    |--------------------------------------------------------------------------
    */

    private function statuses(): array
    {
        return [
            'active',
            'inactive',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Emergency Type Definitions
    |--------------------------------------------------------------------------
    */

    private function emergencyTypes(): array
    {
        return [
            'Fire',
            'Police / Crime',
            'Medical Emergency',
            'Accident',
            'Flood',
            'Typhoon / Severe Weather',
            'Rescue',
            'Disaster',
            'Other Emergency',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Emergency Type → Recommended Categories
    |--------------------------------------------------------------------------
    */

    private function recommendedCategories(
        string $emergencyType
    ): array {
        return match ($emergencyType) {

            'Fire' => [
                'Fire',
                'Rescue',
                'Disaster / Emergency',
            ],

            'Police / Crime' => [
                'Police',
            ],

            'Medical Emergency' => [
                'Medical / Ambulance',
                'Rescue',
            ],

            'Accident' => [
                'Medical / Ambulance',
                'Police',
                'Rescue',
            ],

            'Flood' => [
                'Disaster / Emergency',
                'Rescue',
                'Medical / Ambulance',
            ],

            'Typhoon / Severe Weather' => [
                'Disaster / Emergency',
                'Rescue',
                'Medical / Ambulance',
            ],

            'Rescue' => [
                'Rescue',
                'Disaster / Emergency',
            ],

            'Disaster' => [
                'Disaster / Emergency',
                'Rescue',
                'Medical / Ambulance',
            ],

            default => [
                'Barangay',
                'Disaster / Emergency',
                'Rescue',
                'Other',
            ],
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $search = trim(
            (string) $request->input('search', '')
        );

        $category = trim(
            (string) $request->input('category', '')
        );

        $status = trim(
            (string) $request->input('status', '')
        );

        $emergencyType = trim(
            (string) $request->input('emergency_type', '')
        );


        /*
        |--------------------------------------------------------------------------
        | Retrieve Hotlines
        |--------------------------------------------------------------------------
        */

        $hotlines = EmergencyHotline::query()
            ->with([
                'creator',
                'updater',
            ])

            ->when(
                $search !== '',
                function (Builder $query) use ($search): void {

                    $query->where(function (
                        Builder $searchQuery
                    ) use ($search): void {

                        $searchQuery
                            ->where(
                                'name',
                                'like',
                                '%' . $search . '%'
                            )

                            ->orWhere(
                                'category',
                                'like',
                                '%' . $search . '%'
                            )

                            ->orWhere(
                                'contact_person',
                                'like',
                                '%' . $search . '%'
                            )

                            ->orWhere(
                                'phone_number',
                                'like',
                                '%' . $search . '%'
                            )

                            ->orWhere(
                                'alternate_number',
                                'like',
                                '%' . $search . '%'
                            )

                            ->orWhere(
                                'address',
                                'like',
                                '%' . $search . '%'
                            )

                            ->orWhere(
                                'description',
                                'like',
                                '%' . $search . '%'
                            );
                    });
                }
            )

            ->when(
                $category !== '',
                function (Builder $query) use ($category): void {

                    $query->where(
                        'category',
                        $category
                    );
                }
            )

            ->when(
                $status !== '',
                function (Builder $query) use ($status): void {

                    $query->where(
                        'status',
                        $status
                    );
                }
            )

            ->latest('id')
            ->paginate(10)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | SMART Recommended Hotlines
        |--------------------------------------------------------------------------
        */

        $recommendedHotlines = collect();

        $recommendedCategories = [];

        if ($emergencyType !== '') {

            $recommendedCategories =
                $this->recommendedCategories(
                    $emergencyType
                );

            /*
            |--------------------------------------------------------------------------
            | MySQL / MariaDB category ordering
            |--------------------------------------------------------------------------
            */

            $categoryOrder = collect(
                $recommendedCategories
            )
                ->map(
                    fn (string $item): string =>
                        "'" . addslashes($item) . "'"
                )
                ->implode(', ');

            $recommendedHotlines =
                EmergencyHotline::query()
                    ->where(
                        'status',
                        'active'
                    )
                    ->whereIn(
                        'category',
                        $recommendedCategories
                    )
                    ->orderByRaw(
                        'FIELD(category, ' .
                        $categoryOrder .
                        ')'
                    )
                    ->orderBy('name')
                    ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $totalHotlines =
            EmergencyHotline::count();

        $activeHotlines =
            EmergencyHotline::where(
                'status',
                'active'
            )->count();

        $inactiveHotlines =
            EmergencyHotline::where(
                'status',
                'inactive'
            )->count();


        /*
        |--------------------------------------------------------------------------
        | Categories Actually Used
        |--------------------------------------------------------------------------
        */

        $categories =
            EmergencyHotline::query()
                ->select('category')
                ->whereNotNull('category')
                ->where(
                    'category',
                    '!=',
                    ''
                )
                ->distinct()
                ->orderBy('category')
                ->pluck('category');


        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */

        return view(
            'emergency-hotlines.index',
            compact(
                'hotlines',
                'totalHotlines',
                'activeHotlines',
                'inactiveHotlines',
                'categories',
                'search',
                'category',
                'status',
                'emergencyType',
                'recommendedCategories',
                'recommendedHotlines'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        return view(
            'emergency-hotlines.create',
            [
                'categories' =>
                    $this->categories(),

                'statuses' =>
                    $this->statuses(),

                'emergencyTypes' =>
                    $this->emergencyTypes(),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ): RedirectResponse {

        $validated = $request->validate([

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'category' => [
                'required',
                'string',
                'max:100',
                'in:' . implode(
                    ',',
                    $this->categories()
                ),
            ],

            'contact_person' => [
                'nullable',
                'string',
                'max:150',
            ],

            'phone_number' => [
                'required',
                'string',
                'max:50',
            ],

            'alternate_number' => [
                'nullable',
                'string',
                'max:50',
            ],

            'address' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'status' => [
                'required',
                'in:' . implode(
                    ',',
                    $this->statuses()
                ),
            ],
        ]);


        $userId = $request
            ->user()
            ?->getAuthIdentifier();


        $emergencyHotline =
            EmergencyHotline::create([

                'name' =>
                    $validated['name'],

                'category' =>
                    $validated['category'],

                'contact_person' =>
                    $validated['contact_person']
                    ?? null,

                'phone_number' =>
                    $validated['phone_number'],

                'alternate_number' =>
                    $validated['alternate_number']
                    ?? null,

                'address' =>
                    $validated['address']
                    ?? null,

                'description' =>
                    $validated['description']
                    ?? null,

                'status' =>
                    $validated['status'],

                'created_by' =>
                    $userId,

                'updated_by' =>
                    $userId,
            ]);


        return redirect()
            ->route(
                'emergency-hotlines.index'
            )
            ->with(
                'success',
                'Emergency hotline "' .
                $emergencyHotline->name .
                '" was successfully added.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        EmergencyHotline $emergencyHotline
    ): View {

        $emergencyHotline->load([
            'creator',
            'updater',
        ]);

        return view(
            'emergency-hotlines.show',
            compact('emergencyHotline')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        EmergencyHotline $emergencyHotline
    ): View {

        return view(
            'emergency-hotlines.edit',
            [
                'emergencyHotline' =>
                    $emergencyHotline,

                'categories' =>
                    $this->categories(),

                'statuses' =>
                    $this->statuses(),

                'emergencyTypes' =>
                    $this->emergencyTypes(),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        EmergencyHotline $emergencyHotline
    ): RedirectResponse {

        $validated = $request->validate([

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'category' => [
                'required',
                'string',
                'max:100',
                'in:' . implode(
                    ',',
                    $this->categories()
                ),
            ],

            'contact_person' => [
                'nullable',
                'string',
                'max:150',
            ],

            'phone_number' => [
                'required',
                'string',
                'max:50',
            ],

            'alternate_number' => [
                'nullable',
                'string',
                'max:50',
            ],

            'address' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'status' => [
                'required',
                'in:' . implode(
                    ',',
                    $this->statuses()
                ),
            ],
        ]);


        $validated['updated_by'] =
            $request
                ->user()
                ?->getAuthIdentifier();


        $emergencyHotline->update(
            $validated
        );


        return redirect()
            ->route(
                'emergency-hotlines.index'
            )
            ->with(
                'success',
                'Emergency hotline "' .
                $emergencyHotline->name .
                '" was successfully updated.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Activate
    |--------------------------------------------------------------------------
    */

    public function activate(
        Request $request,
        EmergencyHotline $emergencyHotline
    ): RedirectResponse {

        $emergencyHotline->update([

            'status' =>
                'active',

            'updated_by' =>
                $request
                    ->user()
                    ?->getAuthIdentifier(),
        ]);


        return back()->with(
            'success',
            'Emergency hotline "' .
            $emergencyHotline->name .
            '" is now active.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Deactivate
    |--------------------------------------------------------------------------
    */

    public function deactivate(
        Request $request,
        EmergencyHotline $emergencyHotline
    ): RedirectResponse {

        $emergencyHotline->update([

            'status' =>
                'inactive',

            'updated_by' =>
                $request
                    ->user()
                    ?->getAuthIdentifier(),
        ]);


        return back()->with(
            'success',
            'Emergency hotline "' .
            $emergencyHotline->name .
            '" has been deactivated.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Destroy / Archive
    |--------------------------------------------------------------------------
    |
    | The record is not physically deleted.
    |
    */

    public function destroy(
        Request $request,
        EmergencyHotline $emergencyHotline
    ): RedirectResponse {

        $emergencyHotline->update([

            'status' =>
                'inactive',

            'updated_by' =>
                $request
                    ->user()
                    ?->getAuthIdentifier(),
        ]);


        return redirect()
            ->route(
                'emergency-hotlines.index'
            )
            ->with(
                'success',
                'Emergency hotline "' .
                $emergencyHotline->name .
                '" has been deactivated instead of permanently deleted.'
            );
    }
}
