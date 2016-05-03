<?php

namespace App\Presenters;

use AddressProvidersForm;
use Nette;
use Nette\Application\UI\Form;


/**
 * Class HomepagePresenter
 * @package App\Presenters
 */
class HomepagePresenter extends Nette\Application\UI\Presenter
{

	/** @var  AddressProvidersForm @inject */
	public $providersFormFactory;


	/**
	 * Render homePage
	 */
	public function renderDefault()
	{

		$storage = $this->getSession("addressProviders");

		$this->template->address = $storage->address;
		$this->template->providers = $storage->providers;

		$this["addressProvidersForm"]->setDefaults(["address" => $storage->query]);

	}


	/**
	 * Find providers form
	 * @return Form
	 */
	protected function createComponentAddressProvidersForm()
	{
		$form = $this->providersFormFactory->create();

		$form->onSuccess[] = function (Form $form) {

			//If isAjax() redraw snippet, else...
			$this->redirect('this');
		};

		return $form;
	}
}
