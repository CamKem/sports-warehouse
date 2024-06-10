<?= add('layouts.admin.head', ['title' => $title]) ?>
<div class="admin-header">
    <div class="logo">
        <a href="/" aria-label="Sports Warehouse Home" class="logo">
            <img src="/images/sports-warehouse-logo.svg"
                 alt="Sports Warehouse Logo">
        </a>
    </div>
    <script type="module">
        import { MenuToggle } from '/scripts/menuToggle.js';
        window.onload = () => {
            new MenuToggle('menu-icon', 'menu-nav');
        }
    </script>
    <div class="menu-icon-container">
        <a id="menu-icon">
            <i class="fas fa-bars" aria-hidden="true"></i>
        </a>
    </div>
    <?= add('layouts.admin.menu-nav') ?>
</div>

<div class="admin-breadcrumbs">
    <div class="breadcrumbs">
        <a class="breadcrumb__item" href="<?= route('admin.index') ?>">Admin</a>
        <span>></span>
        <a class="breadcrumb__item" href="<?= route(request()->route()->getName()) ?>"><?= $title ?></a>
    </div>
</div>

<div class="admin-content">
<!--    <h2>--><?php //= $title ?><!--</h2>-->
    <div class="standard-container">
        {{ slot }}
    </div>
</div>
<?= add('layouts.partials.bottom-footer') ?>
<?= add('layouts.partials.flash') ?>
<?= add('layouts.partials.bottom') ?>

