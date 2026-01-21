<?php

namespace NlpTools\Documents;

/**
 * A collection of TrainingDocument objects. It implements many built
 * in php interfaces for ease of use.
 */
class TrainingSet implements \Iterator,\ArrayAccess,\Countable
{
    const CLASS_AS_KEY = 1;
    const OFFSET_AS_KEY = 2;

    // An array that contains all the classes present in the TrainingSet
    protected $classSet;
    protected $documents; // The documents container

    // When iterated upon what should the key be?
    protected $keytype;
    // When iterated upon the currentDocument
    protected $currentDocument;

    public function __construct()
    {
        $this->classSet = array();
        $this->documents = array();
        $this->keytype = self::CLASS_AS_KEY;
    }

    /**
     * Add a document to the set.
     *
     * @param $class The documents actual class
     * @param $d The Document
     * @return void
     */
    public function addDocument($class, DocumentInterface $d)
    {
        $this->documents[] = new TrainingDocument($class,$d);
        $this->classSet[$class] = 1;
    }
    // return the classset
    public function getClassSet()
    {
        return array_keys($this->classSet);
    }

    /**
     * Decide what should be returned as key when iterated upon
     */
    public function setAsKey($what)
    {
        switch ($what) {
            case self::CLASS_AS_KEY:
            case self::OFFSET_AS_KEY:
                $this->keytype = $what;
                break;
            default:
                $this->keytype = self::CLASS_AS_KEY;
                break;
        }
    }

    /**
     * Apply an array of transformations to all documents in this container.
     *
     * @param array An array of TransformationInterface instances
     */
    public function applyTransformations(array $transforms)
    {
        foreach ($this->documents as $doc) {
            foreach ($transforms as $transform) {
                $doc->applyTransformation($transform);
            }
        }
    }

    // ====== Implementation of \Iterator interface =========
    public function rewind(): void
    {
        reset($this->documents);
        $this->currentDocument = current($this->documents);
    }
    public function next(): void
    {
        $this->currentDocument = next($this->documents);
    }
    public function valid(): bool
    {
        return $this->currentDocument!=false;
    }
    public function current(): mixed
    {
        return $this->currentDocument;
    }
    public function key(): mixed
    {
        switch ($this->keytype) {
            case self::CLASS_AS_KEY:
                return $this->currentDocument->getClass();
            case self::OFFSET_AS_KEY:
                return key($this->documents);
            default:
                // we should never be here
                throw new \Exception("Undefined type as key");
        }
    }
    // === Implementation of \Iterator interface finished ===

    // ====== Implementation of \ArrayAccess interface =========
    public function offsetSet(mixed $key, mixed $value): void
    {
        throw new \Exception("Shouldn't add documents this way, add them through addDocument()");
    }
    public function offsetUnset(mixed $key): void
    {
        throw new \Exception("Cannot unset any document");
    }
    public function offsetGet(mixed $key): mixed
    {
        return $this->documents[$key];
    }
    public function offsetExists(mixed $key): bool
    {
        return isset($this->documents[$key]);
    }
    // === Implementation of \ArrayAccess interface finished ===

    // implementation of \Countable interface
    public function count(): int
    {
        return count($this->documents);
    }
}
