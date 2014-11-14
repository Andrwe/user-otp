<?php /** @var $l OC_L10N */ ?>

<?php if (isset($_['otperror'])): ?>
	<div class="error otperror <?php p($_['otperror']['hint']) ?>">
		<?php p($_['otperror']['text']) ?>
	</div>
<?php endif ?>

<?php print_unescaped($this->load($this->findTemplate('', '', 'login', '')[1], $parameters)) ?>
