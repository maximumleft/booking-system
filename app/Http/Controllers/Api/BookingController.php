<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $bookings = $user->bookings()->with('slots')->get();

        return response()->json([
            'data' => $bookings
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'slots' => 'required|array|min:1',
            'slots.*.start_time' => 'required|date|after:now',
            'slots.*.end_time' => 'required|date|after:slots.*.start_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            foreach ($request->slots as $slot) {
                $this->checkSlotConflicts($slot['start_time'], $slot['end_time']);
            }

            $this->checkInternalConflicts($request->slots);

            $booking = Booking::create([
                'user_id' => $request->user()->id,
            ]);

            foreach ($request->slots as $slot) {
                $booking->slots()->create([
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                ]);
            }

            DB::commit();

            $booking->load('slots');

            return response()->json([
                'data' => $booking
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function show(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 403);
        }

        $booking->load('slots');

        return response()->json([
            'data' => $booking
        ]);
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 403);
        }

        $booking->delete();

        return response()->json([
            'message' => 'Booking deleted successfully'
        ]);
    }

    public function updateSlot(Request $request, Booking $booking, BookingSlot $slot): JsonResponse
    {
        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 403);
        }

        if ($slot->booking_id !== $booking->id) {
            return response()->json([
                'error' => 'Slot does not belong to this booking'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $this->checkSlotConflicts(
                $request->start_time,
                $request->end_time,
                $slot->id
            );

            $slot->update([
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            DB::commit();

            return response()->json([
                'data' => $slot
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function addSlot(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $this->checkSlotConflicts($request->start_time, $request->end_time);

            $existingSlots = $booking->slots;
            foreach ($existingSlots as $existingSlot) {
                if ($this->slotsOverlap(
                    $request->start_time,
                    $request->end_time,
                    $existingSlot->start_time,
                    $existingSlot->end_time
                )) {
                    throw new \Exception('Slot conflicts with existing slots in this booking');
                }
            }

            $slot = $booking->slots()->create([
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            DB::commit();

            return response()->json([
                'data' => $slot
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }

    private function checkSlotConflicts(string $startTime, string $endTime, ?int $excludeSlotId = null): void
    {
        $query = BookingSlot::where(function ($query) use ($startTime, $endTime) {
            $query->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            });
        });

        if ($excludeSlotId) {
            $query->where('id', '!=', $excludeSlotId);
        }

        if ($query->exists()) {
            throw new \Exception('Time slot conflicts with existing booking');
        }
    }

    private function checkInternalConflicts(array $slots): void
    {
        for ($i = 0; $i < count($slots); $i++) {
            for ($j = $i + 1; $j < count($slots); $j++) {
                if ($this->slotsOverlap(
                    $slots[$i]['start_time'],
                    $slots[$i]['end_time'],
                    $slots[$j]['start_time'],
                    $slots[$j]['end_time']
                )) {
                    throw new \Exception('Slots within the same booking cannot overlap');
                }
            }
        }
    }

    private function slotsOverlap(string $start1, string $end1, string $start2, string $end2): bool
    {
        return $start1 < $end2 && $start2 < $end1;
    }
}
