<?
/**
 * You can call this block from any view template or layout with the following:
 * <?=$this->bootstrapBlock->render('helloworld', array('foo' => 'bar')); ?>
 */
?>
<p>Hello world from an element.<br />
<?php
if(isset($foo)) {
	echo '...and I have $foo as ' . $foo . ' because you can pass variables to me.';
}
?>
</p>