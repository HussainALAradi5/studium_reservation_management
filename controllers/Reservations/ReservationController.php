<?php

declare(strict_types=1);

namespace App\Controllers\Reservations;

use App\Controllers\Base\Controller;
use App\Dtos\Reservations\CreateReservationDto;
use App\Dtos\Reservations\ReservationQuery;
use App\Dtos\Reservations\StatusChangeDto;
use App\Dtos\Reservations\BookingRequestDto;
use App\Enums\Accounting\PaymentMethod;
use App\Enums\Accounting\TransactionType;
use App\Enums\Reservations\BookingUnit;
use App\Enums\Reservations\ReservationStatus;
use App\Services\Accounting\PaymentService;
use App\Services\Common\AccessPolicy;
use App\Services\Reservations\ReservationService;
use App\Services\Stadiums\StadiumService;
use App\Utilities\Http\Request;
use App\Utilities\Security\Session;

final class ReservationController extends Controller
{
    public function index(Request $request): void
    {
        $user = $this->user();
        $service = new ReservationService();
        $this->render($request, 'reservations/index', [
            'title' => $user->isCustomer() ? 'My bookings' : 'Reservations',
            'page' => $service->list($user, ReservationQuery::fromQuery($request->queryAll())),
            'cancelCutoffHours' => $service->cancelCutoffHours(),
            'filters' => $request->queryAll(),
            'statuses' => ReservationStatus::options(),
            'stadiums' => $user->isCustomer() ? [] : (new StadiumService())->listForSelect($user),
        ]);
    }

    public function show(Request $request): void
    {
        $user = $this->user();
        $service = new ReservationService();
        $reservation = $service->findViewable($user, $request->param('code'));
        $this->render($request, 'reservations/show', [
            'title' => 'Booking ' . $reservation->code,
            'reservation' => $reservation,
            'payments' => (new PaymentService())->listForReservation($reservation),
            'canManage' => AccessPolicy::canManageReservation($user, $reservation),
            'canCancel' => $reservation->status->canTransitionTo(ReservationStatus::CANCELLED) && AccessPolicy::canCancelReservation($user, $reservation, $service->cancelCutoffHours()),
            'canReschedule' => $reservation->status->blocksSlot() && AccessPolicy::canRescheduleReservation($user, $reservation),
            'methods' => PaymentMethod::options(),
            'transactionTypes' => TransactionType::options(),
            'bookingUnits' => BookingUnit::options(),
        ]);
    }

    public function store(Request $request): void
    {
        $reservation = (new ReservationService())->create($this->user(), CreateReservationDto::fromInput($request->all()));
        Session::flash('success', 'Booking requested! The stadium will confirm it shortly.');
        $this->done('Booking requested.', '/reservations/' . $reservation->code, 201, ['code' => $reservation->code]);
    }

    public function confirm(Request $request): void
    {
        (new ReservationService())->confirm($this->user(), $request->param('code'));
        $this->done('Booking confirmed.');
    }

    public function reject(Request $request): void
    {
        (new ReservationService())->reject($this->user(), $request->param('code'), StatusChangeDto::fromInput($request->all(), true));
        $this->done('Booking rejected.');
    }

    public function cancel(Request $request): void
    {
        $user = $this->user();
        (new ReservationService())->cancel($user, $request->param('code'), StatusChangeDto::fromInput($request->all(), !$user->isCustomer()));
        $this->done('Booking cancelled.');
    }

    public function complete(Request $request): void
    {
        (new ReservationService())->complete($this->user(), $request->param('code'));
        $this->done('Booking marked as completed.');
    }

    public function reschedule(Request $request): void
    {
        (new ReservationService())->reschedule($this->user(), $request->param('code'), BookingRequestDto::fromInput($request->all()));
        $this->done('Booking moved to the new time.');
    }
}
