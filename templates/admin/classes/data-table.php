<?= '<?php'; ?>

namespace <?= $this->getNamespace('data-table-admin-generated'); ?>;
use <?= $this->getNamespace('model-implemented'); ?>\<?= $entity->class; ?>;

class <?= $entity->class; ?>DataTable {

    protected $dataTable;

    public function __construct() {
        $this->dataTable = new \Rhino\DataTable\MySqlDataTable(<?= $entity->class; ?>::getPdo(), '<?= $entity->table; ?>');
        $this->dataTable->insertColumn('actions', function($column, $row) {
            // @todo fix delete button, make post, confirm
            return '
                <a href="/admin/<?= $entity->route; ?>/' . $row['id'] . '" class="btn btn-xs btn-default">Edit</a>
                <form action="/admin/<?= $entity->route; ?>/delete/' . $row['id'] . '" method="post">
                    <button class="btn btn-xs btn-link text-danger">Delete</button>
                </form>
            ';
        })->setLabel('Actions');
        $this->dataTable->addColumn('id')->setLabel('ID');
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
        $this->dataTable->addColumn('<?= $attribute->column; ?>')->setLabel('<?= $attribute->label; ?>');
<?php endforeach; ?>
        $this->dataTable->addColumn('created')->setLabel('Created');
        $this->dataTable->addColumn('updated')->setLabel('Updated');
    }

    public function process($request, $response) {
        return $this->dataTable->process($request, $response);
    }

    public function render() {
        return $this->dataTable->render();
    }
}
