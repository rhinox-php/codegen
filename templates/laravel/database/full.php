<?= '<?php'; ?>

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class <?= $entity->getClassName(); ?>Table extends Migration
{
    public function up()
    {
        Schema::create('<?= $entity->getPluralTableName(); ?>', function (Blueprint $table) {
            $table->increments('id');
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\IntAttribute): ?>
            $table->integer('<?= $attribute->getColumnName(); ?>');
<?php elseif ($attribute instanceof \Rhino\Codegen\Attribute\BoolAttribute): ?>
            $table->boolean('<?= $attribute->getColumnName(); ?>');
<?php elseif ($attribute instanceof \Rhino\Codegen\Attribute\DecimalAttribute): ?>
            $table->decimal('<?= $attribute->getColumnName(); ?>', 10, 2);
<?php else: ?>
            $table->string('<?= $attribute->getColumnName(); ?>');
<?php endif; ?>
<?php endforeach; ?>
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('<?= $entity->getPluralTableName(); ?>');
    }
}
