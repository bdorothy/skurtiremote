<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<table class="mainTable cartthrobMatrix <?=$class?>" id="<?=$field_name?>" border="0" cellspacing="0" cellpadding="0">
	<thead>
		<tr>
<?php foreach ($table_headers as $key => $header) : ?>
			<th<?php if ( ! $replace_tag && ! is_numeric($key)) : ?> data-tag="{<?=$key?>}"<?php endif;?>><?=$header?></th>
<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
<?php foreach ($table_rows as $row) : ?>
<?php if ( ! isset($row['data'])) $row = array('data' => $row); ?>
		<tr class="<?=alternator('odd', 'even')?><?php if (isset($row['class'])) : ?> <?=$row['class']?><?php endif; ?>">
<?php foreach ($row['data'] as $i => $col) : ?>
<?php if (is_array($col)) : ?>
			<td <?php if (isset($col['class'])) : ?> class="<?=$col['class']?>"<?php endif; ?><?php if (count($row['data']) == 1) : ?> colspan="<?php echo count($table_headers); ?>"<?php endif; ?>><?=$col['data']?></td>
<?php else : ?>
			<td<?php if (count($row['data']) == 1) : ?> colspan="<?php echo count($table_headers); ?>"<?php endif; ?>><?=$col?></td>
<?php endif; ?>
<?php endforeach; ?>
		</tr>
<?php endforeach; ?>
	</tbody>
</table>