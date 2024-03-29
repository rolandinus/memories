<?php

declare(strict_types=1);

namespace OCA\Memories\Db;

use OCP\IDBConnection;

trait TimelineQueryFolders
{
    protected IDBConnection $connection;

    /**
     * Get the previews inside a given TimelineRoot.
     * The root folder passed to this function must already be populated
     * with the mount points recursively, if this is desired.
     *
     * @param TimelineRoot $root The root to use for the query
     */
    public function getRootPreviews(TimelineRoot $root): array
    {
        $query = $this->connection->getQueryBuilder();

        // SELECT all photos
        $query->select('m.fileid', 'f.etag')->from('memories', 'm');

        // JOIN with the filecache table
        $query->innerJoin('m', 'filecache', 'f', $query->expr()->eq('m.fileid', 'f.fileid'));

        // WHERE these photos are in the user's requested folder recursively
        $query = $this->filterFilecache($query, $root, true, false);

        // MAX 4
        $query->setMaxResults(4);

        // FETCH tag previews
        $rows = $this->executeQueryWithCTEs($query)->fetchAll();

        // Post-process
        foreach ($rows as &$row) {
            $row['fileid'] = (int) $row['fileid'];
        }

        return $rows;
    }
}
