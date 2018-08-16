<?= '<?php'; ?>

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class <?= $entity->class; ?>Table extends Migration
{
    public function up()
    {
        Schema::create('<?= $entity->getPluralTableName(); ?>', function (Blueprint $table) {
            $table->increments('id');
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->is('int')): ?>
            $table->integer('<?= $attribute->column; ?>');
<?php elseif ($attribute->is('bool')): ?>
            $table->boolean('<?= $attribute->column; ?>');
<?php elseif ($attribute->is('decimal')): ?>
            $table->decimal('<?= $attribute->column; ?>', 10, 2);
<?php elseif ($attribute->is('text')): ?>
            $table->text('<?= $attribute->column; ?>');
<?php else: ?>
            $table->string('<?= $attribute->column; ?>');
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
