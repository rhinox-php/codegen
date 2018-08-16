<?= '<?php'; ?>

namespace <?= $this->getNamespace('model-implemented'); ?>;

class <?= $entity->class; ?> extends \<?= $this->getNamespace('model-generated'); ?>\<?= $entity->class; ?> {
    public function __construct() {
    }
}
