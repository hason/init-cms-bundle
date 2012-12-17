<?php

/*
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Networking\InitCmsBundle\Entity\Page;
use Networking\InitCmsBundle\Helper\LanguageSwitcherHelper;

/**
 *
 */
class DefaultController extends Controller
{
	/**
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @return array
	 * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	public function indexAction(Request $request)
	{
		/** @var $page Page */
		$page = $request->get('_content');

		if ($page->getVisibility() != Page::VISIBILITY_PUBLIC) {
			if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
			        throw new AccessDeniedException();
			    }
		}

		if($page->getStatus() != Page::STATUS_PUBLISHED){
			if (false === $this->get('security.context')->isGranted('ROLE_SONATA_ADMIN')) {
				$message = 'The requested page has the status "'.$page->getStatus().'", please login to view page';
				throw $this->createNotFoundException($message);
			}
		}

		return array('page' => $page);
	}

	/**
	 * @Route("/admin/", name="networking_init_cms_admin")
	 */
	public function adminAction()
	{
		$url = $this->generateUrl('sonata_admin_dashboard');

		return $this->redirect($url);
	}

	/**
	 * @Route("/", name="networking_init_cms_home")
	 *
	 * @return array
	 */
	public function homeAction()
	{
		$repository = $this->getDoctrine()->getRepository('NetworkingInitCmsBundle:Page');

		$page = $repository->findOneBy(array('isHome' => true, 'locale' => $this->getRequest()->getLocale()));

		return array('page' => $page);
	}

	/**
	 * @param Request $request
	 * @param $locale
	 *
	 * @Route("/change_admin_language/{locale}", name="change_admin_language", requirements={"locale" = ".+"})
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function changeAdminLanguageAction(Request $request, $locale)
	{
		$request->getSession()->set('admin/_locale', $locale);

		return new RedirectResponse($request->headers->get('referer'));
	}

	/**
	 * @param Request $request
	 * @param $locale
	 *
	 * @Route("/change_language/{locale}", name="networking_init_change_language", requirements={"locale" = ".+"})
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function changeLanguageAction(Request $request, $locale)
	{
		$params = array();

		$oldLocale = $request->getLocale();

		if (($oldLocale == $locale)) {
			return new RedirectResponse($request->headers->get('referer'));
		}
		$translationRoute = $this->getTranslationRoute($request->headers->get('referer'), $locale);

		$request->getSession()->set('_locale', $locale);

		if (!is_array($translationRoute)) {
			$routeName = $translationRoute;
		} else {
			$routeName = $translationRoute['_route'];
			unset($translationRoute['_route']);
			foreach ($translationRoute as $key => $var) {
				$params[$key] = $var;
			}
		}

		$parts = parse_url($request->headers->get('referer'));

		if (array_key_exists('query', $parts) && $parts['query']) {
			parse_str($parts['query'], $query);

			$params = array_merge($query, $params);
		}

		$newURL = $this->get('router')->generate($routeName, $params);

		return new RedirectResponse($newURL);

	}

	/**
	 * @param $referer
	 * @param $locale
	 * @return array|\Symfony\Cmf\Component\Routing\RouteObjectInterface
	 */
	protected function getTranslationRoute($referer, $locale)
	{
		/** @var $languageSwitcherHelper LanguageSwitcherHelper */
		$languageSwitcherHelper = $this->get('networking_init_cms.page.helper.language_switcher');

		$oldURL = $languageSwitcherHelper->getPathInfo($referer);

		return $languageSwitcherHelper->getTranslationRoute($oldURL, $locale);
	}
}