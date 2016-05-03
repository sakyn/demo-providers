<?php


use App\Services\ProvidersApi;
use GoogleGeo\GoogleGeocoding;
use GoogleGeo\GoogleGeoException;
use Nette\Application\UI;
use Nette\Http\Session;

/**
 * Class AddressProvidersForm
 */
class AddressProvidersForm extends UI\Form
{

	/** @var GoogleGeocoding */
	private $googleGeo;

	/** @var ProvidersApi */
	private $providers;

	/** @var  Session $session */
	private $sessionStorage;

	/** @var  $address array addressComponents */
	public $address;


	/**
	 * @param GoogleGeocoding $googleGeo
	 * @param ProvidersApi $providers
	 * @param Session $session
	 */
	public function __construct(GoogleGeocoding $googleGeo, ProvidersApi $providers, Session $session)
	{
		parent::__construct();

		$this->googleGeo = $googleGeo;
		$this->providers = $providers;
		$this->sessionStorage = $session->getSection("addressProviders");
	}


	/**
	 * Address Form
	 * @return UI\Form
	 */
	public function create()
	{

		$form = new UI\Form;

		//$form->addProtection("Platnost formuláře vypršela, odešlete jej prosím znovu");

		$form->addText("address", "Vložte prosím Vaši adresu")
			->setAttribute("placeholder", "Např: Evaldova 10, Šumperk")
			->setRequired('Vložte prosím Vaší adresu')
			->addRule(UI\Form::PATTERN, 'Vložte adresu ve tvaru "ulice číslo domu, město"', '.*[0-9].*')
			->setAttribute("class", "form-control");

		$form->addSubmit('findProviders', 'Vyhledat dodavatele')->setAttribute("class", "btn btn-default");

		$form->onValidate[] = $this->validateAdress;
		$form->onSuccess[] = $this->processForm;

		return $form;
	}


	/**
	 * Fetch Address components from Geo
	 * @param UI\Form $form
	 * @param $values
	 */
	public function validateAdress(UI\Form $form, $values)
	{

		// Temporary storage for API responses
		$this->sessionStorage->query = $values->address;

		try {

			if (!$this->address = $this->googleGeo->getAddress($values->address)) $form->addError("Zadaná adresa nenalezena");

			$this->sessionStorage->address = $this->address;

		} catch (GoogleGeoException $e) {
			//@todo: log $e->getMessage()
			$form->addError("Vyskytla se chyba validace, nepodařilo se ověřit adresu");
		}
	}


	/**
	 * @param UI\Form $form
	 * @param $values
	 */
	public function processForm(UI\Form $form, $values)
	{

		/** @var array providers */
		$this->sessionStorage->providers = $this->providers->getProviders($this->address);

	}
}