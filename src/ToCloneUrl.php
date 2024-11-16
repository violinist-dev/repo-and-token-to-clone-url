<?php

declare(strict_types=1);

namespace Violinist\RepoAndTokenToCloneUrl;

use function peterpostmann\uri\parse_uri;

final class ToCloneUrl
{

    public static function getCloneUrl(string $repo, string $authToken) : string
    {
        $repo_path = $repo;
        $repo_parsed = parse_uri($repo);
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
                    break;
            }
        }
        return $repo_path;
    }
}
