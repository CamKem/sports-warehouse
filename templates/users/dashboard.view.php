<script type="module">
    import ModalManager from '/scripts/modalManager.js';

    new ModalManager('delete-form', 'delete');
</script>
<?= add('modals.confirmation', ['action' => 'delete']) ?>
<section>
    <h2>User Dashboard</h2>
    <div class="standard-container">
        <div class="mb-8 form-bottom dashboard-header">
            <p>Welcome, <?= $user->username; ?>!</p>
            <?php if ($user->isAdmin()): ?>
                <p>You have admin privileges, visit
                    <a class="general-text" style="color: var(--blue-color);"
                       href="<?= route('admin.index') ?>"> Admin Panel</a></p>
            <?php endif; ?>
        </div>
        <?php if ($orders->isEmpty()): ?>
            <p class="text-section">You have no orders yet.</p>
        <?php else: ?>
            <ul class="content-form">
                <p class="content-form-heading">Here are some of your recent
                    orders:</p>
                <?php foreach ($orders as $order): ?>
                    <li class="content-form-item">
                        <div class="flex-center align-start">
                            <a class="order-link"
                               href="<?= route('orders.show', ['order' => $order->id]) ?>">
                                Order #<?= $order->id; ?>
                                - <?= date_format(date_create($order->created_at), 'd/m/Y'); ?>
                            </a>
                        </div>
                        <div class="status">
                            <?= $order->status; ?>
                        </div>
                        <div class="form-buttons">
                            <button class="desktop-only">
                                <a href="<?= route('orders.show', ['order' => $order->id]) ?>">
                                    Track Order
                                </a>
                            </button>
                            <form method="post"
                                  id="delete-form"
                                  name="delete-form"
                                  action="<?= route('orders.destroy', ['order' => $order->id]) ?>">
                                <input type="hidden" name="_method"
                                       value="DELETE">
                                <button type="submit" class="delete-button">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
                <?php if ($orders->links() && count($orders->links()) > 1): ?>
                    <li class="content-form-divider">
                        <?= add('layouts.partials.pagination', ['items' => $orders]) ?>
                    </li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>