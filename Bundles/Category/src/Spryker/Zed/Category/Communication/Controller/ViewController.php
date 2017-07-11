<?php


namespace Spryker\Zed\Category\Communication\Controller;


use Spryker\Zed\Kernel\Communication\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * @method \Spryker\Zed\Category\Business\CategoryFacade getFacade()
 * @method \Spryker\Zed\Category\Communication\CategoryCommunicationFactory getFactory()
 * @method \Spryker\Zed\Category\Persistence\CategoryQueryContainer getQueryContainer()
 */
class ViewController extends AbstractController
{

    const QUERY_PARAM_ID_CATEGORY = 'id-category';

    /**
     * @param Request $request
     *
     * @return array
     */
    public function indexAction(Request $request)
    {

        $idCategory = $request->query->getInt(static::QUERY_PARAM_ID_CATEGORY);

        $categoryTransfer = $this->getFacade()
            ->read($idCategory);

        if (!$categoryTransfer) {
            throw new NotFoundHttpException();
        }

        $localeTransfer = $this->getFactory()->getCurrentLocale();
        $readPlugins = $this->getFactory()
            ->getRelationReadPluginStack();

        $renderedRelations = [];
        foreach ($readPlugins as $readPlugin) {
            $renderedRelations[] = [
                'name' => $readPlugin->getRelationName(),
                'items' => $readPlugin->getRelations($categoryTransfer, $localeTransfer)
            ];
        }

        return $this->viewResponse([
            'category' => $categoryTransfer,
            'renderedRelations' => $renderedRelations
        ]);
    }

}