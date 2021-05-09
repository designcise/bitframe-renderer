<?php $this->parent('assets::nested-fetch-0', $this->getData()); ?>

<?php $this->append('js'); ?>
alert('#1');
<?php $this->end(); ?>
<?= $this->fetch('assets::prepend-fetch-1', ['id' => 1]); ?>
<?= $this->fetch('assets::prepend-fetch-2', ['id' => 1]); ?>
<?= $this->fetch('assets::append-fetch-1', ['id' => 1]); ?>
<?= $this->fetch('assets::append-fetch-2', ['id' => 1]); ?>