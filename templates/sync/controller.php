<?= '<?php'; ?>

namespace <?= $this->getNamespace('controller-sync'); ?>;

class SyncController extends \<?= $this->getNamespace('controller-admin-generated'); ?>\AbstractController {
    public function sync() {
        ini_set('memory_limit', '1g');
        ini_set("zlib.output_compression", "On");
        $since = new \DateTimeImmutable('@0');
        if (isset($this->request->get('filter')['since'])) {
            $since = $this->input->dateTime('filter.since');
        }
        $this->response->callback(function() use($since) {
            header('Content-Type: application/json');
            $first = true;
            echo '{"data":[' . PHP_EOL;
            foreach ([
<?php foreach ($entities as $entity): ?>
                        \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->getClassName(); ?>::class,
<?php endforeach; ?>
                    ] as $modelClass) {
                foreach ($modelClass::sync($since) as $i => $entity) {
                    if (!$first) {
                        echo ',' . PHP_EOL;
                    }
                    echo json_encode($entity);
                    flush();
                    $first = false;
                }
            }
            echo PHP_EOL . ']}' . PHP_EOL;
        });
    }
}
