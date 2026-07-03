<?php
// Ensure $user is defined to satisfy Intelephense and prevent runtime errors
if (!isset($user) || !($user instanceof \App\Models\User\User)) {
    $user = null; 
}

if (!$user) {
    echo "<p class='text-red-500'>User information not found.</p>";
    return;
}

$cardTitle = 'Profile Information';

$cardBody = "
    <div class='space-y-4'>
        <p><strong>Username:</strong> " . htmlspecialchars($user->userName) . "</p>
        <p><strong>Email:</strong> " . htmlspecialchars($user->email) . "</p>
        <p><strong>Phone:</strong> " . htmlspecialchars($user->phoneNumber) . "</p>
        <p><strong>Account Status:</strong> " . ($user->isActive ? 'Active' : 'Disabled') . "</p>
    </div>
";

$cardFooter = "
    <div class='flex gap-2'>
        <button class='btn-modern btn-solid'>Edit Profile</button>
        <button class='btn-modern btn-outline'>Change Password</button>
    </div>
";

include __DIR__ . '/../partials/GenericCard.php';
?>