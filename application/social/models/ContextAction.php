<?php
/**
 * @package Social
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author  joseph.rouffet
 * @author  matthieu.napoli
 * @package Social
 */
class Social_Model_ContextAction extends Social_Model_Action
{

    const QUERY_GENERIC_ACTION = 'genericAction';

    /**
     * The action is planned
     */
    const PROGRESS_PLANNED = 'planned';

    /**
     * The action is launched
     */
    const PROGRESS_LAUNCHED = 'launched';

    /**
     * The action is achieved
     */
    const PROGRESS_ACHIEVED = 'achieved';

    /**
     * @var DateTime|null
     */
    protected $launchDate;

    /**
     * @var DateTime|null
     */
    protected $targetDate;

    /**
     * @var DateTime|null
     */
    protected $achievementDate;

    /**
     * @see Social_Model_ContextAction::PROGRESS_PLANNED
     * @see Social_Model_ContextAction::PROGRESS_LAUNCHED
     * @see Social_Model_ContextAction::PROGRESS_ACHIEVED
     * @var string
     */
    protected $progress;

    /**
     * Nom du responsable de l'action
     * @var string
     */
    protected $personInCharge;

    /**
     * Modele d'action associé
     * @var Social_Model_GenericAction
     */
    protected $genericAction;

    /**
     * @var Social_Model_ContextActionKeyFigure[]|Collection
     */
    protected $keyFigures;


    /**
     * @param Social_Model_GenericAction $genericAction
     */
    public function __construct(Social_Model_GenericAction $genericAction)
    {
        parent::__construct();
        $this->setGenericAction($genericAction);
        $this->setProgress(self::PROGRESS_PLANNED);
        $this->keyFigures = new ArrayCollection();
    }

    /**
     * @param Social_Model_GenericAction $genericAction
     */
    public function setGenericAction(Social_Model_GenericAction $genericAction)
    {
        $this->genericAction = $genericAction;
        $genericAction->addContextAction($this);
    }


    /**
     * @return Social_Model_GenericAction
     */
    public function getGenericAction()
    {
        return $this->genericAction;
    }

    /**
     * @return DateTime|null
     */
    public function getLaunchDate()
    {
        return $this->launchDate;
    }

    /**
     * @param DateTime|null $launchDate
     */
    public function setLaunchDate(DateTime $launchDate = null)
    {
        $this->launchDate = $launchDate;
    }

    /**
     * @return DateTime|null
     */
    public function getTargetDate()
    {
        return $this->targetDate;
    }

    /**
     * @param DateTime|null $targetDate
     */
    public function setTargetDate(DateTime $targetDate = null)
    {
        $this->targetDate = $targetDate;
    }

    /**
     * @return DateTime|null
     */
    public function getAchievementDate()
    {
        return $this->achievementDate;
    }

    /**
     * @param DateTime|null $achievementDate
     */
    public function setAchievementDate(DateTime $achievementDate = null)
    {
        $this->achievementDate = $achievementDate;
    }

    /**
     * @return string
     */
    public function getPersonInCharge()
    {
        return $this->personInCharge;
    }

    /**
     * @param string $personInCharge
     */
    public function setPersonInCharge($personInCharge = null)
    {
        $this->personInCharge = $personInCharge;
    }

    /**
     * @see Social_Model_ContextAction::PROGRESS_PLANNED
     * @see Social_Model_ContextAction::PROGRESS_LAUNCHED
     * @see Social_Model_ContextAction::PROGRESS_ACHIEVED
     * @return string
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * @param string $progress
     * @see Social_Model_ContextAction::PROGRESS_PLANNED
     * @see Social_Model_ContextAction::PROGRESS_LAUNCHED
     * @see Social_Model_ContextAction::PROGRESS_ACHIEVED
     * @throws Core_Exception_InvalidArgument
     */
    public function setProgress($progress)
    {
        if ($progress != self::PROGRESS_PLANNED
            && $progress != self::PROGRESS_LAUNCHED
            && $progress != self::PROGRESS_ACHIEVED
        ) {
            throw new Core_Exception_InvalidArgument("Invalid progress");
        }
        $this->progress = (string) $progress;
    }

    /**
     * @return Social_Model_ContextActionKeyFigure[]
     */
    public function getKeyFigures()
    {
        return $this->keyFigures;
    }

    /**
     * @param Social_Model_ContextActionKeyFigure $keyFigure
     */
    public function addKeyFigure(Social_Model_ContextActionKeyFigure $keyFigure)
    {
        if (! $this->hasKeyFigure($keyFigure)) {
            $this->keyFigures->add($keyFigure);
        }
    }

    /**
     * @param Social_Model_ContextActionKeyFigure $keyFigure
     */
    public function removeGenericAction(Social_Model_ContextActionKeyFigure $keyFigure)
    {
        if ($this->hasKeyFigure($keyFigure)) {
            $this->keyFigures->removeElement($keyFigure);
        }
    }

    /**
     * @param Social_Model_ContextActionKeyFigure $keyFigure
     * @return boolean
     */
    public function hasKeyFigure(Social_Model_ContextActionKeyFigure $keyFigure)
    {
        return $this->keyFigures->contains($keyFigure);
    }

}
