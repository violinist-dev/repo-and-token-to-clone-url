<?php

declare(strict_types=1);

namespace Violinist\RepoAndTokenToCloneUrl;

use function peterpostmann\uri\parse_uri;

final class ToCloneUrl
{

    /**
     * A static helper to help you on your way.
     */
    public static function fromRepoAndToken(string $repo, string $authToken) : string
    {
        $repo_path = $repo;
        $repo_parsed = parse_uri($repo);
        $has_replaced = false;
        if (!empty($repo_parsed)) {
            switch ($repo_parsed['_protocol']) {
                case 'git@bitbucket.org':
                    $path = sprintf('/%s', $repo_parsed['path']);
                    $repo_path = self::replaceForBitbucket($authToken, $path);
                    $has_replaced = true;
                    break;

                case 'git@github.com':
                    [$username, $password] = self::getCredentials($authToken, 'x-access-token');
                    $repo_path = sprintf(
                        'https://%s:%s@github.com/%s',
                        $username,
                        $password,
                        $repo_parsed['path']
                    );
                    $has_replaced = true;
                    break;
            }
            if (!$has_replaced) {
                switch ($repo_parsed['host']) {
                    case 'www.github.com':
                    case 'github.com':
                        [$username, $password] = self::getCredentials($authToken, 'x-access-token');
                        $repo_path = sprintf(
                            'https://%s:%s@github.com%s',
                            $username,
                            $password,
                            $repo_parsed["path"]
                        );
                        break;

                    case 'www.gitlab.com':
                    case 'gitlab.com':
                        [$username, $password] = self::getCredentials($authToken, 'oauth2');
                        $repo_path = sprintf('https://%s:%s@gitlab.com%s', $username, $password, $repo_parsed["path"]);
                        break;

                    case 'www.bitbucket.org':
                    case 'bitbucket.org':
                        $repo_path = self::replaceForBitbucket($authToken, $repo_parsed['path']);
                        break;

                    default:
                        $port = 443;
                        if ($repo_parsed['scheme'] === 'http') {
                            $port = 80;
                        }
                        if (!empty($repo_parsed["port"])) {
                            $port = $repo_parsed["port"];
                        }
                        [$username, $password] = self::getCredentials($authToken, 'oauth2');
                        $repo_path = sprintf(
                            '%s://%s:%s@%s:%d%s',
                            $repo_parsed["scheme"],
                            $username,
                            $password,
                            $repo_parsed["host"],
                            $port,
                            $repo_parsed["path"]
                        );
                        // If using a more standard way, meaning the scheme
                        // matches its default port so to speak, we can just
                        // use the host and path.
                        if ($port === 443 && $repo_parsed['scheme'] === 'https') {
                            $repo_path = sprintf(
                                'https://%s:%s@%s%s',
                                $username,
                                $password,
                                $repo_parsed["host"],
                                $repo_parsed["path"]
                            );
                        }
                        // Same for 80 and http.
                        if ($port === 80 && $repo_parsed['scheme'] === 'http') {
                            $repo_path = sprintf(
                                'http://%s:%s@%s%s',
                                $username,
                                $password,
                                $repo_parsed["host"],
                                $repo_parsed["path"]
                            );
                        }
                        break;
                }
            }
        }
        return $repo_path;
    }

    /**
     * Split a token into (username, password) credentials.
     *
     * If the token contains a colon it is treated as "username:password" (or
     * "email:password"). The username portion is URL-encoded so that special
     * characters such as "@" in an email address are safe to embed in a URL.
     * When there is no colon the supplied default username is returned with
     * the full token as the password.
     *
     * @return array{0: string, 1: string}
     */
    private static function getCredentials(string $authToken, string $defaultUsername): array
    {
        $colonPos = strpos($authToken, ':');
        if ($colonPos !== false) {
            return [
                rawurlencode(substr($authToken, 0, $colonPos)),
                substr($authToken, $colonPos + 1),
            ];
        }
        return [$defaultUsername, $authToken];
    }

    private static function replaceForBitbucket(string $authToken, string $path)
    {
        // Atlassian API tokens (which start with ATAT) need a different user.
        if (strpos($authToken, 'ATAT') === 0) {
            $repo_path = sprintf(
                'https://x-bitbucket-api-token-auth:%s@bitbucket.org%s',
                $authToken,
                $path
            );
        } else {
            [$username, $password] = self::getCredentials($authToken, 'x-token-auth');
            $repo_path = sprintf('https://%s:%s@bitbucket.org%s', $username, $password, $path);
        }
        // We also want to ensure it ends with .git.
        if (substr($repo_path, -4) !== '.git') {
            $repo_path .= '.git';
        }
        return $repo_path;
    }
}
