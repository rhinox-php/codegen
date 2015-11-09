<form action="" method="post">
    <?php foreach ($entity->getAttributes() as $attribute): ?>

    <div class="form-group">
        <label><?= $attribute->getLabel(); ?></label>
        <input type="text" class="form-control" name="<?= $attribute->getName(); ?>" />
    </div>

    <?php endforeach; ?>
</form>