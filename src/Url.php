<?php

declare(strict_types=1);

namespace SebastianFeldmann\Git;

/**
 * Class Url
 *
 * Represents a valid repository URL either http or ssh.
 *
 * @package SebastianFeldmann\Git
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/sebastianfeldmann/git
 * @since   Class available since Release 3.8.0
 */
final class Url
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $scheme;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $repoName;

    public function __construct(string $url)
    {
        $parsed         = $this->parseUrl($url);
        $this->url      = $url;
        $this->scheme   = $parsed['scheme'] ?? '';
        $this->user     = $parsed['user'] ?? '';
        $this->host     = $parsed['host'] ?? '';
        $this->path     = $parsed['path'] ?? '';
        $this->repoName = $this->parseRepoName($this->path);
    }

    /**
     * Is the given url an SSH clone URL
     *
     * @param  string $url
     * @return bool
     */
    public static function isSSHUrl(string $url): bool
    {
        // should not contain http
        // should at least contain one colon
        return strpos($url, 'http') === false && strpos($url, ':') !== false;
    }

    /**
     * Returns the full url
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Returns only the scheme
     *
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * Returns only the user
     *
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * Returns only the host
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Returns only the path
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Returns the repo name (last path segment of the url)
     *
     * @return string
     */
    public function getRepoName(): string
    {
        return $this->repoName;
    }

    /**
     * Detect the url components
     *
     * @param  string $url
     * @return array
     */
    private function parseUrl(string $url): array
    {
        // By default, GitHub and gitlab urls can't be parsed by parse_url,
        // so we have to make sure we end up with parsable urls
        if (self::isSSHUrl($url)) {
            $url = $this->convertToValidUrl($url);
        }

        return parse_url($url);
    }

    /**
     * This converts GitHub and gitlab ssh urls to parsable urls
     *
     * @param  string $url
     * @return string
     */
    private function convertToValidUrl(string $url): string
    {
        $url = $this->addMissingScheme($url);
        return $this->replaceColonWithSlash($url);
    }

    /**
     * Find the repo name within the url path
     *
     * @param  string $path
     * @return string
     */
    private function parseRepoName(string $path): string
    {
        if (empty($path)) {
            return '';
        }

        $lastSlashPosition = strrpos($path, '/');
        return str_replace('.git', '', substr($path, $lastSlashPosition + 1));
    }

    /**
     * This will add the ssh scheme if it is missing
     *
     * @param  string $url
     * @return string
     */
    private function addMissingScheme(string $url): string
    {
        return strpos($url, 'ssh://') !== false ? $url : 'ssh://' . $url;
    }

    /**
     * Replace the git@github.com:user/repo with github.com/user/repo
     *
     * @param  string $url
     * @return string
     */
    private function replaceColonWithSlash(string $url): string
    {
        $lastColonPosition = strrpos($url, ':');
        return substr($url, 0, $lastColonPosition) . '/' . substr($url, $lastColonPosition + 1);
    }
}