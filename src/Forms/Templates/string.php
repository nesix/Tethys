<?php
/**
 * @var string $name
 * @var string $value
 * @var string $placeholder
 */
$placeholder = trim($placeholder ?? '');
?>
<input type="text" name="<?=$name?>" value="<?=htmlspecialchars($value ?: '')?>"<? if ($placeholder) {?> placeholder="<?=htmlspecialchars($placeholder)?>"<? } ?> />
