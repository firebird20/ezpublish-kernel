<?php
/**
 * File containing the DoctrineDatabase user metadata criterion handler class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler;

use eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use RuntimeException;

/**
 * User metadata criterion handler
 */
class UserMetadata extends CriterionHandler
{
    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return boolean
     */
    public function accept( Criterion $criterion )
    {
        return $criterion instanceof Criterion\UserMetadata;
    }

    /**
     * Generate query expression for a Criterion this handler accepts
     *
     * accept() must be called before calling this method.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriteriaConverter $converter
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return \eZ\Publish\Core\Persistence\Database\Expression
     */
    public function handle( CriteriaConverter $converter, SelectQuery $query, Criterion $criterion )
    {
        switch ( $criterion->target )
        {
            case Criterion\UserMetadata::MODIFIER:
                return $query->expr->in(
                    $this->dbHandler->quoteColumn( "creator_id", "ezcontentobject_version" ),
                    $criterion->value
                );

            case Criterion\UserMetadata::GROUP:
                $subSelect = $query->subSelect();
                $subSelect
                    ->select(
                        $this->dbHandler->quoteColumn( "contentobject_id", "t1" )
                    )->from(
                        $query->alias(
                            $this->dbHandler->quoteTable( "ezcontentobject_tree" ),
                            "t1"
                        )
                    )->innerJoin(
                        $query->alias(
                            $this->dbHandler->quoteTable( "ezcontentobject_tree" ),
                            "t2"
                        ),
                        $query->expr->like(
                            "t1.path_string",
                            $query->expr->concat(
                                "t2.path_string",
                                $query->bindValue( "%" )
                            )
                        )
                    )->where(
                        $query->expr->in(
                            $this->dbHandler->quoteColumn( "contentobject_id", "t2" ),
                            $criterion->value
                        )
                    );

                return $query->expr->in(
                    $this->dbHandler->quoteColumn( "owner_id", "ezcontentobject" ),
                    $subSelect
                );

            case Criterion\UserMetadata::OWNER:
                return $query->expr->in(
                    $this->dbHandler->quoteColumn( "owner_id", "ezcontentobject" ),
                    $criterion->value
                );
        }

        throw new RuntimeException( "Invalid target criterion encountered:'" . $criterion->target . "'" );
    }
}
