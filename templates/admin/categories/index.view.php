<script type="module">
    import ModalManager from "/scripts/modalManager.js";

    new ModalManager('admin-category-create', 'category-create');
    new ModalManager('delete-form', 'delete');
</script>
<?= add('modals.confirmation', ['action' => 'delete']) ?>
<?= add('modals.admin-category-create') ?>
<section>
    <div class="admin-form-actions">
        <form name="admin-category-create">
            <button>Create category</button>
        </form>
        <form class="search-form" action="<?= route('admin.categories.index') ?>"
              method="get">
            <label for="search-bar" class="sr-only">Search categorys</label>
            <input type="text" name="search" id="search-bar"
                   value="<?= request()->get('search') ?>"
                   placeholder="Search categories">
            <button type="submit" id="search-button">
                <i class="fas fa-search" aria-hidden="true"></i>
            </button>
        </form>
    </div>
    <?php if ($categories->isEmpty()): ?>
        <p class="text-section">No categories have been found.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead class="admin-table-heading">
            <tr class="admin-heading-row">
                <th>Name</th>
                <th>Slug</th>
                <th>Created</th>
                <th>Updated</th>
                <th style="width: 100px">Actions</th>
            </tr>
            </thead>
            <?php foreach ($categories as $category): ?>
                <tr class="admin-table-row">
                    <td><?= $category->name; ?></td>
                    <td><?= $category->slug; ?></td>
                    <td><?= date('d M Y', strtotime($category->created_at)) ?></td>
                    <td><?= date('d M Y', strtotime($category->updated_at)) ?></td>
                    <td class="form-buttons">
                        <script type="module">
                            import ModalManager from "/scripts/modalManager.js";

                            new ModalManager('category-edit', 'edit');
                        </script>
                        <form name="category-edit">
                            <button>Edit</button>
                        </form>
                        <form method="post"
                              id="delete-form-<?= $category->id ?>"
                              name="delete-form"
                              action="<?= route('admin.categories.destroy', ['id' => $category->id]) ?>">
                            <input type="hidden" name="_method"
                                   value="DELETE">
                            <button type="submit" class="delete-button">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                </a>
            <?php endforeach; ?>
            <?php if ($categories->links() && count($categories->links()) > 1): ?>
                <tfoot>
                <tr>
                    <td colspan="6">
                        <?= add('layouts.partials.pagination', ['items' => $categories]) ?>
                    </td>
                </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    <?php endif; ?>
</section>