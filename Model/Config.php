<?php
/**
 * Copyright (c) 2026 Magenx Commerce. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magenx\Sitemap\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Typed, store-scoped reader for the sitemap URL rewriting settings.
 *
 * Every value is read at store-view scope, which is what makes a per-locale
 * path prefix possible: one Magento store view per storefront locale.
 */
class Config
{
    private const XML_PATH_ENABLED = 'magenx_sitemap/general/enabled';
    private const XML_PATH_STORE_PATH_PREFIX = 'magenx_sitemap/general/store_path_prefix';
    private const XML_PATH_STRIP_URL_SUFFIX = 'magenx_sitemap/general/strip_url_suffix';
    private const XML_PATH_CATEGORY_PATH_PREFIX = 'magenx_sitemap/category/path_prefix';
    private const XML_PATH_PRODUCT_PATH_PREFIX = 'magenx_sitemap/product/path_prefix';
    private const XML_PATH_PRODUCT_URL_KEY_ONLY = 'magenx_sitemap/product/url_key_only';
    private const XML_PATH_CMS_PATH_PREFIX = 'magenx_sitemap/cms/path_prefix';

    private const XML_PATH_PRODUCT_URL_SUFFIX = 'catalog/seo/product_url_suffix';
    private const XML_PATH_CATEGORY_URL_SUFFIX = 'catalog/seo/category_url_suffix';
    private const XML_PATH_CMS_HOME_PAGE = 'web/default/cms_home_page';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Is URL rewriting active for this store view.
     */
    public function isEnabled(int $storeId): bool
    {
        return $this->flag(self::XML_PATH_ENABLED, $storeId);
    }

    /**
     * Leading storefront path segment, e.g. "en" for /en/product/blue-jacket.
     */
    public function getStorePathPrefix(int $storeId): string
    {
        return $this->trimmedPath(self::XML_PATH_STORE_PATH_PREFIX, $storeId);
    }

    /**
     * Should the catalog url suffix (".html") be removed from sitemap URLs.
     */
    public function isStripUrlSuffix(int $storeId): bool
    {
        return $this->flag(self::XML_PATH_STRIP_URL_SUFFIX, $storeId);
    }

    public function getCategoryPathPrefix(int $storeId): string
    {
        return $this->trimmedPath(self::XML_PATH_CATEGORY_PATH_PREFIX, $storeId);
    }

    public function getProductPathPrefix(int $storeId): string
    {
        return $this->trimmedPath(self::XML_PATH_PRODUCT_PATH_PREFIX, $storeId);
    }

    /**
     * Should a product URL be reduced to its url_key (last path segment).
     */
    public function isProductUrlKeyOnly(int $storeId): bool
    {
        return $this->flag(self::XML_PATH_PRODUCT_URL_KEY_ONLY, $storeId);
    }

    public function getCmsPathPrefix(int $storeId): string
    {
        return $this->trimmedPath(self::XML_PATH_CMS_PATH_PREFIX, $storeId);
    }

    public function getProductUrlSuffix(int $storeId): string
    {
        return (string)$this->value(self::XML_PATH_PRODUCT_URL_SUFFIX, $storeId);
    }

    public function getCategoryUrlSuffix(int $storeId): string
    {
        return (string)$this->value(self::XML_PATH_CATEGORY_URL_SUFFIX, $storeId);
    }

    /**
     * Identifier of the CMS page serving as the store home page.
     */
    public function getCmsHomePageIdentifier(int $storeId): string
    {
        return trim((string)$this->value(self::XML_PATH_CMS_HOME_PAGE, $storeId));
    }

    private function flag(string $path, int $storeId): bool
    {
        return $this->scopeConfig->isSetFlag($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    private function value(string $path, int $storeId): mixed
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    private function trimmedPath(string $path, int $storeId): string
    {
        return trim(trim((string)$this->value($path, $storeId)), '/');
    }
}
