<?php

/**
 * Partial pagination yang dipakai bersama oleh Daftar Buku dan Admin.
 * Variabel yang harus disiapkan view pemanggil: $pager (array) dengan isi:
 *   ariaLabel, ulClass, linkClass (kelas tambahan untuk .page-link, boleh ''),
 *   prevLabel, nextLabel, current, total, urlFor (fungsi: nomor halaman -> URL)
 */

$pagerCurrent = (int) $pager['current'];
$pagerTotal = (int) $pager['total'];
$pagerUrl = $pager['urlFor'];
$pagerLink = trim('page-link ' . ($pager['linkClass'] ?? ''));
?>
<?php if ($pagerTotal > 1) : ?>
    <nav aria-label="<?= htmlspecialchars($pager['ariaLabel']); ?>" class="mt-4">
        <ul class="<?= htmlspecialchars($pager['ulClass']); ?>">

            <li class="page-item <?= $pagerCurrent <= 1 ? 'disabled' : ''; ?>">
                <a class="<?= $pagerLink; ?>" href="<?= htmlspecialchars($pagerUrl(max(1, $pagerCurrent - 1))); ?>">
                    <?= $pager['prevLabel']; ?>
                </a>
            </li>

            <?php for ($i = 1; $i <= $pagerTotal; $i++) : ?>
                <li class="page-item <?= $i === $pagerCurrent ? 'active' : ''; ?>">
                    <a class="<?= $pagerLink; ?>" href="<?= htmlspecialchars($pagerUrl($i)); ?>">
                        <?= $i; ?>
                    </a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?= $pagerCurrent >= $pagerTotal ? 'disabled' : ''; ?>">
                <a class="<?= $pagerLink; ?>" href="<?= htmlspecialchars($pagerUrl(min($pagerTotal, $pagerCurrent + 1))); ?>">
                    <?= $pager['nextLabel']; ?>
                </a>
            </li>

        </ul>
    </nav>
<?php endif; ?>
