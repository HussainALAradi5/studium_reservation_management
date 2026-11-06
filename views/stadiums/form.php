<?php
/**
 * @var \App\Models\Stadiums\Stadium|null $stadium
 * @var \App\Models\Regional\City[] $cities
 * @var \App\Models\Users\User[] $managers
 * @var bool $canAssignManager
 * @var array<string, string> $sports
 * @var array<string, string> $surfaces
 * @var int[] $slotOptions
 * @var array $map
 */
use App\Constants\Common\RegexPatterns;

$cityOptions = [];
foreach ($cities as $city) {
    $cityOptions[$city->code] = $city->name;
}
if ($stadium && !isset($cityOptions[$stadium->address->cityCode])) {
    $cityOptions[$stadium->address->cityCode] = $stadium->address->cityName . ' (inactive)';
}
$managerOptions = [];
foreach ($managers as $manager) {
    $managerOptions[$manager->code] = $manager->fullName . ' · ' . $manager->phone;
}
$slotChoices = [];
foreach ($slotOptions as $minutes) {
    $slotChoices[$minutes] = $minutes . ' minutes';
}
$address = $stadium?->address;

$fields = [
    ['section' => 'Details'],
    ['name' => 'name', 'label' => 'Stadium name', 'required' => true, 'full' => true, 'value' => $stadium?->name, 'maxlength' => 120, 'icon' => 'stadium'],
    ['name' => 'sportType', 'label' => 'Sport', 'type' => 'select', 'required' => true, 'options' => $sports, 'value' => $stadium?->sportType->value],
    ['name' => 'surfaceType', 'label' => 'Surface', 'type' => 'select', 'required' => true, 'options' => $surfaces, 'value' => $stadium?->surfaceType->value],
    ['name' => 'capacity', 'label' => 'Capacity (players)', 'type' => 'number', 'required' => true, 'min' => 2, 'max' => 200, 'value' => $stadium?->capacity ?? 10, 'icon' => 'users'],
    ['name' => 'slotMinutes', 'label' => 'Slot length', 'type' => 'select', 'required' => true, 'options' => $slotChoices, 'value' => $stadium?->slotMinutes ?? 60, 'hint' => 'Hourly bookings follow this grid.'],
    ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'full' => true, 'value' => $stadium?->description, 'maxlength' => 2000],

    ['section' => 'Pricing & hours'],
    ['name' => 'pricePerHour', 'label' => 'Price per hour', 'type' => 'number', 'required' => true, 'step' => '0.001', 'min' => '0.001', 'value' => $stadium?->pricePerHour, 'icon' => 'cash'],
    ['name' => 'pricePerDay', 'label' => 'Price per full day', 'type' => 'number', 'step' => '0.001', 'min' => '0.001', 'value' => $stadium?->pricePerDay, 'icon' => 'cash', 'hint' => 'Leave empty to rent by the hour only.'],
    ['name' => 'openingTime', 'label' => 'Opens at', 'type' => 'time', 'required' => true, 'value' => $stadium?->openingTime ?? '08:00', 'minuteStep' => 30],
    ['name' => 'closingTime', 'label' => 'Closes at', 'type' => 'time', 'required' => true, 'value' => $stadium?->closingTime ?? '23:00', 'minuteStep' => 30, 'hint' => 'Earlier than opening = closes after midnight.'],
];
if ($canAssignManager) {
    $fields[] = ['name' => 'managerCode', 'label' => 'Manager', 'type' => 'select', 'options' => $managerOptions, 'emptyOption' => 'No manager yet', 'value' => $stadium?->managerCode, 'full' => true];
}
$fields[] = ['name' => 'isActive', 'label' => 'Open for bookings', 'type' => 'switch', 'value' => $stadium?->isActive ?? true, 'full' => true];
$fields = [
    ...$fields,
    ['section' => 'Address'],
    ['name' => 'cityCode', 'label' => 'City', 'type' => 'select', 'required' => true, 'options' => $cityOptions, 'emptyOption' => 'Choose a city', 'value' => $address?->cityCode, 'icon' => 'city'],
    ['name' => 'street', 'label' => 'Street / road', 'required' => true, 'value' => $address?->street, 'maxlength' => 120],
    ['name' => 'building', 'label' => 'Building', 'value' => $address?->building, 'pattern' => RegexPatterns::BUILDING_NUMBER],
    ['name' => 'block', 'label' => 'Block', 'value' => $address?->block, 'pattern' => RegexPatterns::BUILDING_NUMBER],
    ['name' => 'area', 'label' => 'Area', 'value' => $address?->area, 'full' => true],
    ['name' => 'latitude', 'type' => 'hidden', 'value' => $address?->latitude, 'attrs' => ['data-map-lat' => true]],
    ['name' => 'longitude', 'type' => 'hidden', 'value' => $address?->longitude, 'attrs' => ['data-map-lng' => true]],
];

$mapPicker = '<div class="form__section"><h3 class="form__section-title">Map location</h3>'
    . '<div class="map map--picker" data-map-picker data-default-lat="' . e($map['lat']) . '" data-default-lng="' . e($map['lng']) . '" data-default-zoom="' . e($map['zoom']) . '" data-tile-url="' . e($map['tileUrl']) . '"></div>'
    . '<div class="row-between"><span class="small muted" data-map-status>' . ($address?->hasLocation() ? 'Pinned at ' . e($address->latitude) . ', ' . e($address->longitude) : 'Click the map to drop a pin.') . '</span>'
    . '<div class="btn-group">' . button('Use my location', ['variant' => 'glass', 'size' => 'sm', 'icon' => 'navigation', 'attrs' => ['data-map-locate' => true]])
    . button('Clear pin', ['variant' => 'ghost', 'size' => 'sm', 'icon' => 'x', 'attrs' => ['data-map-clear' => true]]) . '</div></div>'
    . '<div class="field" data-field="latitude"><div class="field__error" data-error-for="latitude" role="alert"></div></div></div>';
?>
<?= partial('ui/pageHeader', [
    'eyebrow' => $stadium ? 'Editing ' . $stadium->code : 'New stadium',
    'eyebrowIcon' => 'stadium',
    'title' => $stadium ? $stadium->name : 'Add a stadium',
    'subtitle' => 'Details, pricing, opening hours, and the exact location on the map.',
]) ?>
<div class="card" data-animate="fade-up" style="max-width:920px">
    <div class="card__body">
        <?= partial('forms/genericForm', [
            'action' => $stadium ? "/api/stadiums/{$stadium->code}" : '/api/stadiums',
            'method' => $stadium ? 'PUT' : 'POST',
            'columns' => 2,
            'fields' => $fields,
            'after' => $mapPicker,
            'submitText' => $stadium ? 'Save changes' : 'Create stadium',
            'loadingText' => 'Saving…',
            'cancelHref' => url('/manage/stadiums'),
        ]) ?>
    </div>
</div>
