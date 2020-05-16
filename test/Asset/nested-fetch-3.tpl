<?php $this->parent('assets::nested-fetch-2', $this->getData()); ?>

<?php $this->append('js'); ?>
alert('#3');
<?php $this->end(); ?>
<?= $this->fetch('assets::prepend-fetch-1', ['id' => 3]); ?>
<?= $this->fetch('assets::prepend-fetch-2', ['id' => 3]); ?>
<?= $this->fetch('assets::append-fetch-1', ['id' => 3]); ?>
<?= $this->fetch('assets::append-fetch-2', ['id' => 3]); ?>