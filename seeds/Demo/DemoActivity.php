<?php

declare(strict_types=1);

namespace App\Seeds\Demo;

/**
 * Demo activity, scheduled relative to the import day (day 0 = today) so the site always shows past
 * games, upcoming bookings, and requests waiting for a manager. Per stadium the ranges never overlap.
 */
final class DemoActivity
{
    /**
     * [code, stadium, customer, day offset, start time, hours (hourly) or null, days (daily) or null,
     *  status, paid amount, payment method, status reason, notes]
     */
    public const RESERVATIONS = [
        // Seef Arena: 25.000/hour, 180.000/day, 08:00–02:00
        ['RSV-DEMO0001', 'Seef Arena', 'c1', -10, '18:00', 2, null, 'completed', 50.0, 'cash', null, 'Weekly team practice'],
        ['RSV-DEMO0002', 'Seef Arena', 'c2', -7, '20:00', 2, null, 'completed', 50.0, 'card', null, null],
        ['RSV-DEMO0003', 'Seef Arena', 'c3', -3, '19:00', 1, null, 'completed', 25.0, 'online', null, null],
        ['RSV-DEMO0004', 'Seef Arena', 'c4', -2, '17:00', 2, null, 'cancelled', 0.0, null, 'Team could not make it', null],
        ['RSV-DEMO0005', 'Seef Arena', 'c1', 1, '18:00', 2, null, 'confirmed', 20.0, 'cash', null, 'Bring the orange bibs'],
        ['RSV-DEMO0006', 'Seef Arena', 'c5', 2, '20:00', 2, null, 'pending', 0.0, null, null, 'Friendly match'],
        ['RSV-DEMO0007', 'Seef Arena', 'c6', 5, null, null, 1, 'confirmed', 0.0, null, null, 'School sports day'],
        // Riffa Padel Club: 12.000/hour, hourly only, 07:00–23:00, 30-minute slots
        ['RSV-DEMO0008', 'Riffa Padel Club', 'c7', -6, '09:00', 1.5, null, 'completed', 18.0, 'card', null, null],
        ['RSV-DEMO0009', 'Riffa Padel Club', 'c8', -4, '18:30', 1, null, 'completed', 12.0, 'cash', null, null],
        ['RSV-DEMO0010', 'Riffa Padel Club', 'c8', -1, '07:00', 1, null, 'rejected', 0.0, null, 'Court maintenance that morning', null],
        ['RSV-DEMO0011', 'Riffa Padel Club', 'c2', 1, '10:00', 1, null, 'confirmed', 12.0, 'online', null, null],
        ['RSV-DEMO0012', 'Riffa Padel Club', 'c3', 3, '19:00', 2, null, 'pending', 0.0, null, null, 'Doubles with friends'],
        // Muharraq Sports Hall: 18.000/hour, 120.000/day, 09:00–22:00
        ['RSV-DEMO0013', 'Muharraq Sports Hall', 'c4', -9, '16:00', 2, null, 'completed', 36.0, 'bank_transfer', null, null],
        ['RSV-DEMO0014', 'Muharraq Sports Hall', 'c5', -5, '10:00', 2, null, 'completed', 20.0, 'cash', null, null],
        ['RSV-DEMO0015', 'Muharraq Sports Hall', 'c6', 2, '18:00', 2, null, 'confirmed', 36.0, 'card', null, null],
        ['RSV-DEMO0016', 'Muharraq Sports Hall', 'c7', 4, null, null, 2, 'pending', 0.0, null, null, 'Basketball tournament'],
    ];

    /** [code, stadium, customer, rating, comment] — every author has a completed game at that stadium. */
    public const REVIEWS = [
        ['REV-DEMO0001', 'Seef Arena', 'c1', 5, 'Excellent turf and bright floodlights. Booking took seconds.'],
        ['REV-DEMO0002', 'Seef Arena', 'c2', 4, 'Great pitch, parking gets busy after 8 pm.'],
        ['REV-DEMO0003', 'Seef Arena', 'c3', 5, 'Clean changing rooms and friendly staff.'],
        ['REV-DEMO0004', 'Riffa Padel Club', 'c7', 4, 'Good courts, the 30-minute slots are handy.'],
        ['REV-DEMO0005', 'Riffa Padel Club', 'c8', 5, 'Best padel court in Riffa.'],
        ['REV-DEMO0006', 'Muharraq Sports Hall', 'c4', 4, 'Nice wooden floor, good air conditioning.'],
        ['REV-DEMO0007', 'Muharraq Sports Hall', 'c5', 3, 'Decent hall, the scoreboard needs fixing.'],
    ];

    /** [code, stadium, category, amount, day offset, description] */
    public const EXPENSES = [
        ['EXP-DEMO0001', 'Seef Arena', 'maintenance', 40.0, -8, 'Turf brushing and line painting'],
        ['EXP-DEMO0002', 'Seef Arena', 'utilities', 35.5, -3, 'Floodlight electricity'],
        ['EXP-DEMO0003', 'Riffa Padel Club', 'equipment', 25.0, -5, 'New padel balls'],
        ['EXP-DEMO0004', 'Muharraq Sports Hall', 'salaries', 60.0, -6, 'Weekend attendant'],
    ];

    /**
     * [code, recipient key, template, title, body, related type, related reservation code or stadium name, is read]
     * For related type "stadium" the stadium's code is looked up by name at import time.
     */
    public const NOTIFICATIONS = [
        ['NTF-DEMO0001', 'm1', 'reservation.requested', 'New booking request', 'Hussain Kadhem requested Seef Arena for 2 hours (Friendly match).', 'reservation', 'RSV-DEMO0006', false],
        ['NTF-DEMO0002', 'm1', 'reservation.requested', 'New booking request', 'Ahmed Isa requested Muharraq Sports Hall for 2 days.', 'reservation', 'RSV-DEMO0016', false],
        ['NTF-DEMO0003', 'm1', 'review.created', 'New review', 'Ali Hassan rated Seef Arena 5/5.', 'stadium', 'Seef Arena', true],
        ['NTF-DEMO0004', 'm2', 'reservation.requested', 'New booking request', 'Mohammed Saleh requested Riffa Padel Club for 2 hours.', 'reservation', 'RSV-DEMO0012', false],
        ['NTF-DEMO0005', 'c1', 'reservation.confirmed', 'Booking confirmed', 'Your booking at Seef Arena tomorrow at 18:00 (2 hours) is confirmed.', 'reservation', 'RSV-DEMO0005', false],
        ['NTF-DEMO0006', 'c1', 'payment.recorded', 'Payment received', '20.000 BHD was received for your booking at Seef Arena.', 'reservation', 'RSV-DEMO0005', false],
        ['NTF-DEMO0007', 'c4', 'reservation.cancelled', 'Booking cancelled', 'The booking at Seef Arena was cancelled. Reason: Team could not make it', 'reservation', 'RSV-DEMO0004', true],
        ['NTF-DEMO0008', 'c8', 'reservation.rejected', 'Booking rejected', 'Your request for Riffa Padel Club was rejected. Reason: Court maintenance that morning', 'reservation', 'RSV-DEMO0010', false],
        ['NTF-DEMO0009', 'c6', 'reservation.confirmed', 'Booking confirmed', 'Your full-day booking at Seef Arena is confirmed.', 'reservation', 'RSV-DEMO0007', false],
        ['NTF-DEMO0010', 'c3', 'reservation.completed', 'Thanks for playing!', 'Your game at Seef Arena is complete. Rate the stadium to help other players.', 'reservation', 'RSV-DEMO0003', true],
    ];
}
