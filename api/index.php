<?php
/**
 * Vercel PHP serverless entry point.
 * All requests are forwarded to the main application router in /public/index.php.
 * The router reads $_SERVER['REQUEST_URI'] to decide what to execute.
 */
require_once __DIR__ . '/../public/index.php';
