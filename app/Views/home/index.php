<?php
/**
 * Landing Page View (hero layout – BR Architects style)
 * Add your RLHI image to: assets/images/hero-bg.jpg
 */
require_once __DIR__ . '/../../../includes/ui_public.php';
$base = defined('BASE_PATH') ? BASE_PATH : '';
ui_public_head('RLHI - Material Request System');
ui_public_hero_layout($base);
