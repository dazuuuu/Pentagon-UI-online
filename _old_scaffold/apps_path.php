<?php

function pq_project_root(): string
{
    return dirname(__DIR__);
}

function pq_public_root(): string
{
    return pq_project_root() . '/public';
}

function pq_apps_root(): string
{
    return pq_project_root() . '/apps';
}

function pq_asset_url(string $path): string
{
    $path = '/' . ltrim($path, '/');
    return $path;
}
