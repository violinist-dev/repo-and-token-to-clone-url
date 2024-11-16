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
                    $repo_path = sprintf('https://x-token-auth:%s@bitbucket.org/%s', $authToken, $repo_parsed['path']);
                    if (strlen($authToken) < 50 && strpos($authToken, ':') !== false) {
                        $repo_path = sprintf(
                            'https://%s@bitbucket.org/%s',
                            $authToken,
                            $repo_parsed['path']
                        );
                    }
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

                }
            }
        }
        return $repo_path;
    }
}
