<?php
/**
 * File containing the DoctrineDatabase language code criterion handler class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\CriterionHandler;

use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\CriterionHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

/**
 * LanguageCode criterion handler
 */
class LanguageCode extends CriterionHandler
{
    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    private $maskGenerator;

    /**
     * Construct from language mask generator
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator $maskGenerator
     */
    public function __construct( DatabaseHandler $dbHandler, MaskGenerator $maskGenerator )
    {
        parent::__construct( $dbHandler );

        $this->maskGenerator = $maskGenerator;
    }

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return boolean
     */
    public function accept( Criterion $criterion )
    {
        return $criterion instanceof Criterion\LanguageCode;
    }

    /**
     * Generate query expression for a Criterion this handler accepts
     *
     * accept() must be called before calling this method.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\CriteriaConverter $converter
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return \eZ\Publish\Core\Persistence\Database\Expression
     */
    public function handle( CriteriaConverter $converter, SelectQuery $query, Criterion $criterion )
    {
        $languages = array_flip( $criterion->value );
        /** @var $criterion \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LanguageCode */
        $languages['always-available'] = $criterion->matchAlwaysAvailable;

        return $query->expr->gt(
            $query->expr->bitAnd(
                $this->dbHandler->quoteColumn( 'language_mask', 'ezcontentobject' ),
                // @todo: Use a cached version of mask generator when implemented
                $this->maskGenerator->generateLanguageMask( $languages )
            ),
            0
        );
    }
}
