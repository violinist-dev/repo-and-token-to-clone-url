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
                    $repo_path = sprintf(
                        'https://x-access-token:%s@github.com/%s',
                        $authToken,
                        $repo_parsed['path']
                    );
                    $has_replaced = true;
                    break;
            }
            if (!$has_replaced) {
                switch ($repo_parsed['host']) {
                    case 'www.github.com':
                    case 'github.com':
                        $repo_path = sprintf(
                            'https://x-access-token:%s@github.com%s',
                            $authToken,
                            $repo_parsed["path"]
                        );
                        break;

                    case 'www.gitlab.com':
                    case 'gitlab.com':
                        $repo_path = sprintf('https://oauth2:%s@gitlab.com%s', $authToken, $repo_parsed["path"]);
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
                        $repo_path = sprintf(
                            '%s://oauth2:%s@%s:%d%s',
                            $repo_parsed["scheme"],
                            $authToken,
                            $repo_parsed["host"],
                            $port,
                            $repo_parsed["path"]
                        );
                        // If using a more standard way, meaning the scheme
                        // matches its default port so to speak, we can just
                        // use the host and path.
                        if ($port === 443 && $repo_parsed['scheme'] === 'https') {
                            $repo_path = sprintf(
                                'https://oauth2:%s@%s%s',
                                $authToken,
                                $repo_parsed["host"],
                                $repo_parsed["path"]
                            );
                        }
                        // Same for 80 and http.
                        if ($port === 80 && $repo_parsed['scheme'] === 'http') {
                            $repo_path = sprintf(
                                'http://oauth2:%s@%s%s',
                                $authToken,
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

    private static function replaceForBitbucket(string $authToken, string $path)
    {
        $repo_path = sprintf('https://x-token-auth:%s@bitbucket.org%s', $authToken, $path);
        if (strlen($authToken) < 50 && strpos($authToken, ':') !== false) {
            $repo_path = sprintf(
                'https://%s@bitbucket.org%s',
                $authToken,
                $path
            );
        }
        // We also want to ensure it ends with .git.
        if (substr($repo_path, -4) !== '.git') {
            $repo_path .= '.git';
        }
        return $repo_path;
    }
}
