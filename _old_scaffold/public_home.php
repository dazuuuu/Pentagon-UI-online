<?php
require_once dirname(__DIR__) . '/apps/backend/bootstrap.php';

$siteName = Settings::get('site_name', config('app_name', 'Pentagon Quest'));
$siteLogo = Settings::get('site_logo', '/assets/logo-placeholder.svg');
$heroTitle = Settings::get('hero_title', 'Plan the journey of a lifetime');
$heroSubtitle = Settings::get('hero_subtitle', 'Luxury safaris, beach escapes, and curated travel experiences made easy.');
$heroCtaLabel = Settings::get('hero_cta_label', 'Explore tours');
$heroCtaUrl = Settings::get('hero_cta_url', '/packages/');
$heroSecondaryLabel = Settings::get('hero_secondary_label', 'View destinations');
$heroSecondaryUrl = Settings::get('hero_secondary_url', '/destinations/');

$featured = [];
try {
    $db = Database::get();
    $featured = $db->query('SELECT * FROM tours ORDER BY id DESC LIMIT 3')->fetchAll();
} catch (Throwable $e) {
    $featured = [];
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($siteName) ?> · Travel with confidence</title>
  <meta name="description" content="Modern travel planning with admin-managed destinations, tours, and booking updates.">
  <link rel="stylesheet" href="/assets/site.css">
</head>
<body>
  <header class="hero-shell">
    <nav class="topbar">
      <a class="brand" href="/">
        <?php if ($siteLogo): ?><img src="<?= e($siteLogo) ?>" alt="<?= e($siteName) ?> logo">
        <?php else: ?><span><?= e($siteName) ?></span><?php endif; ?>
      </a>
      <div class="nav-links">
        <a href="/packages/">Packages</a>
        <a href="/destinations/">Destinations</a>
        <a href="/admin/login.php">Admin</a>
      </div>
    </nav>

    <div class="hero-grid">
      <div>
        <p class="eyebrow">Pentagon Quest</p>
        <h1><?= e($heroTitle) ?></h1>
        <p class="hero-copy"><?= e($heroSubtitle) ?></p>
        <div class="hero-actions">
          <a class="btn btn-primary" href="<?= e($heroCtaUrl) ?>"><?= e($heroCtaLabel) ?></a>
          <a class="btn btn-secondary" href="<?= e($heroSecondaryUrl) ?>"><?= e($heroSecondaryLabel) ?></a>
        </div>
      </div>
      <div class="hero-card">
        <h2>Why travelers choose us</h2>
        <ul>
          <li>Tailored itineraries and transparent pricing</li>
          <li>Live admin updates for bookings and requests</li>
          <li>Reliable email delivery through SMTP settings</li>
        </ul>
      </div>
    </div>
  </header>

  <main class="content-wrap">
    <section class="section-block">
      <div class="section-head">
        <div>
          <p class="eyebrow">Featured tours</p>
          <h2>Curated experiences ready for your next adventure</h2>
        </div>
        <a href="/packages/">View all packages</a>
      </div>
      <div class="card-grid">
        <?php if ($featured): foreach ($featured as $tour): ?>
          <article class="card">
            <h3><?= e($tour['title'] ?? 'Featured package') ?></h3>
            <p><?= e($tour['description'] ?? 'A premium travel experience managed from the admin panel.') ?></p>
            <div class="meta-row">
              <span><?= e($tour['duration'] ?? 'Flexible duration') ?></span>
              <a href="/packages/">Reserve</a>
            </div>
          </article>
        <?php endforeach; else: ?>
          <article class="card"><h3>Content is ready to be published</h3><p>Use the admin panel to create tours, destinations, and email templates.</p></article>
        <?php endif; ?>
      </div>
    </section>
  </main>
</body>
</html>
