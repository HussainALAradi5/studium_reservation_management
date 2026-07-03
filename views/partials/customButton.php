<?php
$btnText = $btnText ?? 'Submit';
$btnId = $btnId ?? 'submit-btn-' . uniqid(); 
$btnVariant = $btnVariant ?? 'btn-solid'; 
$btnSize = $btnSize ?? 'btn-md';

$classes = "btn-modern $btnVariant $btnSize";
?>

<button 
    id="<?php echo htmlspecialchars($btnId); ?>" 
    type="button"
    class="<?php echo htmlspecialchars($classes); ?>">
    <?php echo htmlspecialchars($btnText); ?>
</button>