<?php

declare(strict_types=1);

namespace App\Seeds\Demo;

/**
 * Demo accounts for a public demo: 2 managers and 8 customers. Codes are fixed so a re-import is
 * ignored; passwords are generated per install (bin/export-demo-sql.php --with-people) and kept in the
 * git-ignored deploy/demo-accounts.txt. Emails use the reserved example.com domain.
 */
final class DemoPeople
{
    /** key => [code, role, full name, username, email, phone] */
    public const PEOPLE = [
        'm1' => ['USR-DEMOM001', 'manager', 'Yousif Al Mannai', 'mgr.yousif', 'yousif.manager@example.com', '+97339000101'],
        'm2' => ['USR-DEMOM002', 'manager', 'Noora Haddad', 'mgr.noora', 'noora.manager@example.com', '+97339000102'],
        'c1' => ['USR-DEMOC001', 'customer', 'Ali Hassan', 'ali.hassan', 'ali.hassan@example.com', '+97339000201'],
        'c2' => ['USR-DEMOC002', 'customer', 'Fatema Jaffar', 'fatema.jaffar', 'fatema.jaffar@example.com', '+97339000202'],
        'c3' => ['USR-DEMOC003', 'customer', 'Mohammed Saleh', 'mohammed.saleh', 'mohammed.saleh@example.com', '+97339000203'],
        'c4' => ['USR-DEMOC004', 'customer', 'Maryam Abdulla', 'maryam.abdulla', 'maryam.abdulla@example.com', '+97339000204'],
        'c5' => ['USR-DEMOC005', 'customer', 'Hussain Kadhem', 'hussain.kadhem', 'hussain.kadhem@example.com', '+97339000205'],
        'c6' => ['USR-DEMOC006', 'customer', 'Zainab Ebrahim', 'zainab.ebrahim', 'zainab.ebrahim@example.com', '+97339000206'],
        'c7' => ['USR-DEMOC007', 'customer', 'Ahmed Isa', 'ahmed.isa', 'ahmed.isa@example.com', '+97339000207'],
        'c8' => ['USR-DEMOC008', 'customer', 'Sara Khalil', 'sara.khalil', 'sara.khalil@example.com', '+97339000208'],
    ];

    /** Stadium name => manager key (applied only while the stadium has no manager). */
    public const STADIUM_MANAGERS = [
        'Seef Arena' => 'm1',
        'Muharraq Sports Hall' => 'm1',
        'Riffa Padel Club' => 'm2',
    ];
}
