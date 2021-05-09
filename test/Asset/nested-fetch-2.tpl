<?php $this->parent('assets::nested-fetch-1', $this->getData()); ?>

<?php $this->append('js'); ?>
alert('#2');
<?php $this->end(); ?>
<?= $this->fetch('assets::prepend-fetch-1', ['id' => 2]); ?>
<?= $this->fetch('assets::prepend-fetch-2', ['id' => 2]); ?>
<?= $this->fetch('assets::append-fetch-1', ['id' => 2]); ?>
<?= $this->fetch('assets::append-fetch-2', ['id' => 2]); ?>