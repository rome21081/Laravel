<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Models\Accident;
use App\Models\Vehicle;
use App\Models\AccidentImage;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccidentController extends BaseController
{
    public function __construct()
    {
        // Require authentication for all accident routes
        //$this->middleware('auth:sanctum');
    }

    /**
     * Resolve vehicle type by ID or name
     */
    private function resolveVehicleType($input)
    {
        if (!$input) return false;

        if (is_numeric($input)) {
            return VehicleType::find($input)?->id ?? false;
        }

        return VehicleType::whereRaw("LOWER(type_name) = ?", [strtolower($input)])
                ->value('id') ?? false;
    }

    /**
     * Save base64 image to storage
     */
    private function saveBase64Image($base64, $folder = 'uploads/accidents')
    {
        if (!$base64) return null;

        if (!file_exists(public_path($folder))) {
            mkdir(public_path($folder), 0755, true);
        }

        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $m)) {
            $ext = $m[1];
            $base64 = substr($base64, strpos($base64, ',') + 1);
        } else {
            $ext = 'png';
        }

        $data = base64_decode($base64);
        if (!$data) return null;

        $filename = "$folder/" . uniqid("img_", true) . ".$ext";
        file_put_contents(public_path($filename), $data);

        return $filename;
    }

    /**
     * List all accidents (paginated)
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?? 50;

        $accidents = Accident::with([
                'vehicles.type', 
                'images', 
                'type'
            ])
            ->orderByDesc('accident_id')
            ->paginate($limit);

        return response()->json($accidents);
    }

    /**
     * Store new accident
     */
    public function store(Request $request)
    {
        $typeId = $this->resolveVehicleType($request->type_id ?? $request->accidentType);
        if (!$typeId) {
            return response()->json(['success' => false, 'message' => 'Invalid vehicle type'], 400);
        }

        try {
            DB::beginTransaction();

            $accident = Accident::create([
                'case_number' => $request->case_number ?? 'CASE-' . uniqid(),
                'type_id' => $typeId,
                'accident_date' => $request->accident_date ?? now(),
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'address' => $request->address,
                'description' => $request->description,
                'severity' => $request->severity ?? 'Minor',
                'reported_by' => $request->reported_by,
            ]);

      
            if ($request->vehicle) {
                $v = $request->vehicle;
                $vehType = $this->resolveVehicleType($v['vehicle_type_id'] ?? $v['type'] ?? $typeId);

                Vehicle::create([
                    'accident_id' => $accident->accident_id,
                    'plate_number' => $v['plate_number'] ?? null,
                    'vehicle_type_id' => $vehType,
                    'brand' => $v['brand'] ?? null,
                    'model' => $v['model'] ?? null,
                    'year' => $v['year'] ?? null,
                    'color' => $v['color'] ?? null,
                    'notes' => $v['notes'] ?? null
                ]);
            }

            // Add image if provided
            if ($request->image) {
                $path = $this->saveBase64Image($request->image);
                if ($path) {
                    AccidentImage::create([
                        'accident_id' => $accident->accident_id,
                        'file_path' => $path
                    ]);
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'accident_id' => $accident->accident_id], 201);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $ex->getMessage()], 500);
        }
    }

    /**
     * Update accident (route model binding)
     */
    public function update(Request $request, Accident $accident)
    {
        $typeId = $this->resolveVehicleType($request->type_id ?? $request->accidentType);
        if (!$typeId) {
            return response()->json(['success' => false, 'message' => 'Invalid vehicle type'], 400);
        }

        $accident->update([
            'type_id' => $typeId,
            'accident_date' => $request->accident_date,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'address' => $request->address,
            'description' => $request->description,
            'severity' => $request->severity ?? 'Minor'
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete accident
     */
    public function destroy(Accident $accident)
    {
        $accident->delete();
        return response()->json(['success' => true]);
    }
}
