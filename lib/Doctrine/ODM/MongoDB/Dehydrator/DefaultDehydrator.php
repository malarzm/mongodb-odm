<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\ODM\MongoDB\Dehydrator;

use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\PersistentCollection\PersistentCollectionInterface;
use Doctrine\ODM\MongoDB\Types\Type;

class DefaultDehydrator implements Dehydrator
{
    /**
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * @param DocumentManager $documentManager
     */
    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * Dehydrate object to MongoDB data.
     *
     * @param object $document
     * @return array
     */
    public function dehydrate($document)
    {
        $dehydrated = [];
        $class = $this->documentManager->getClassMetadata(get_class($document));
        foreach ($class->fieldMappings as $fieldName => $mapping) {
            if (! empty($mapping['isInverseSide'])) {
                continue;
            }
            $value = $class->reflFields[$mapping['fieldName']]->getValue($document);
            $dbValue = null;
            if (! isset($mapping['association'])) {
                $dbValue = Type::getType($mapping['type'])->convertToDatabaseValue($value);
            } elseif ($class->isSingleValuedAssociation($fieldName) && $value !== null) {
                $dbValue = $this->dehydrateAssociation($value, $mapping);
            } elseif ($class->isCollectionValuedAssociation($fieldName)) {
                $dbValue = $this->dehydrateCollection($value, $mapping);
            }
            $dehydrated[$mapping['name']] = $dbValue;
        }
        return $dehydrated;
    }

    private function dehydrateCollection($coll, $mapping)
    {
        if ($coll === null) {
            return null;
        }
        if ($coll instanceof PersistentCollectionInterface && ! $coll->isInitialized() && ! $coll->isDirty()) {
            return $coll->getMongoData();
        }
        $mergeWith = [];
        if ($coll instanceof PersistentCollectionInterface && ! $coll->isInitialized() && $coll->isDirty()) {
            // this is only possible for things stored as BSON array
            $mergeWith = $coll->getMongoData();
        }
        // do not initialize
        $coll = $coll instanceof PersistentCollectionInterface ? $coll->unwrap() : $coll;
        $coll = $coll instanceof Collection ? $coll->toArray() : $coll;
        $coll = array_map([$this, 'dehydrateAssociation'], $coll);
        if (empty($mergeWith)) {
            return $coll;
        }
        foreach ($coll as $c) {
            $mergeWith[] = $c;
        }
        return $mergeWith;
    }

    private function dehydrateAssociation($value, $mapping)
    {
        return ! empty($mapping['reference'])
            ? $this->documentManager->createDBRef($value, $mapping)
            : $this->dehydrate($value);
    }
}
