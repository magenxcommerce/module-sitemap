<?php
/**
 * Copyright (c) 2026 Magenx Commerce. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magenx\Sitemap\Model;

/**
 * Turns a Magento sitemap path into a headless storefront path.
 *
 * Magento emits url_rewrite request paths ("men/tops.html",
 * "men/tops/blue-jacket.html"); the Next.js storefront serves
 * "/{locale}/category/men/tops" and "/{locale}/product/blue-jacket".
 *
 * Pure string work — no queries, no side effects. The returned path is
 * relative and has no leading slash, because Magento appends it to a base
 * URL that already ends in one.
 */
class UrlRewriter
{
    public const TYPE_STORE = 'store';
    public const TYPE_CATEGORY = 'category';
    public const TYPE_PRODUCT = 'product';
    public const TYPE_CMS = 'cms';

    public function __construct(
        private readonly Config $config
    ) {
    }

    /**
     * @param string $url Sitemap item URL as produced by the core item provider.
     * @param string $entityType One of the TYPE_* constants.
     * @param int $storeId Store view the sitemap is generated for.
     */
    public function rewrite(string $url, string $entityType, int $storeId): string
    {
        // Absolute URLs are not ours to touch (a provider may already emit one).
        if (preg_match('#^[a-z][a-z0-9+.\-]*://#i', $url) === 1) {
            return $url;
        }

        $path = trim(trim($url), '/');
        $path = $this->stripSuffix($path, $entityType, $storeId);

        if ($entityType === self::TYPE_PRODUCT && $this->config->isProductUrlKeyOnly($storeId)) {
            $separator = strrpos($path, '/');
            if ($separator !== false) {
                $path = substr($path, $separator + 1);
            }
        }

        $segments = [$this->config->getStorePathPrefix($storeId)];

        // The store URL item and the CMS home page are both the storefront root:
        // with a locale prefix that is "/en", never "/en/home".
        if (!$this->isStoreRoot($path, $entityType, $storeId)) {
            $segments[] = $this->getEntityPathPrefix($entityType, $storeId);
            $segments[] = $path;
        }

        return implode('/', array_filter($segments, static fn (string $segment): bool => $segment !== ''));
    }

    private function stripSuffix(string $path, string $entityType, int $storeId): string
    {
        if ($path === '' || !$this->config->isStripUrlSuffix($storeId)) {
            return $path;
        }

        $suffix = match ($entityType) {
            self::TYPE_PRODUCT => $this->config->getProductUrlSuffix($storeId),
            self::TYPE_CATEGORY => $this->config->getCategoryUrlSuffix($storeId),
            default => '',
        };

        if ($suffix !== '' && str_ends_with($path, $suffix)) {
            $path = substr($path, 0, -strlen($suffix));
        }

        return $path;
    }

    private function isStoreRoot(string $path, string $entityType, int $storeId): bool
    {
        if ($entityType === self::TYPE_STORE || $path === '') {
            return true;
        }

        if ($entityType !== self::TYPE_CMS) {
            return false;
        }

        $homeIdentifier = $this->config->getCmsHomePageIdentifier($storeId);

        return $homeIdentifier !== '' && $path === $homeIdentifier;
    }

    private function getEntityPathPrefix(string $entityType, int $storeId): string
    {
        return match ($entityType) {
            self::TYPE_PRODUCT => $this->config->getProductPathPrefix($storeId),
            self::TYPE_CATEGORY => $this->config->getCategoryPathPrefix($storeId),
            self::TYPE_CMS => $this->config->getCmsPathPrefix($storeId),
            default => '',
        };
    }
}
