<?php
/**
 * Ana sayfa: home_blocks tablosundaki her satir, turune gore views/blocks/ altindaki sablonla basilir.
 * @var array<int, array<string, mixed>> $blocks
 * @var array<string, mixed> $data
 */
use App\Core\View;

foreach ($blocks as $block) {
    $template = 'blocks/' . preg_replace('/[^a-z_]/', '', (string) $block['type']);
    if (is_file(BASE_PATH . '/views/' . $template . '.php')) {
        echo View::renderFile($template, ['block' => $block, 'data' => $data]);
    }
}
