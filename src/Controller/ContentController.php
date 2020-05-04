<?php


namespace App\Controller;


use App\Entity\Product;
use Exception;
use Proxies\__CG__\App\Entity\Store;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContentController extends AbstractController
{
    /**
     * @Route("/getProducts/{store_id}")
     * @param $store_id
     * @return Exception|JsonResponse
     */
    public function getProducts($store_id) {
        if ($store_id) {
            $repository = $this->getDoctrine()
                ->getRepository(Product::class);

            $products = $repository->findBy(array('Store' => $store_id));

            return new JsonResponse($products);
        } else {
            return new Exception('You must provide a store identity.');
        }
    }

    /**
     * @Route("/getStores")
     * @return Response
     */
    public function getStores() {
        $repository = $this->getDoctrine()
            ->getRepository(Store::class);

        $stores = $repository->findAll();

        return new JsonResponse($stores);
    }

    /**
     * @Route("/insertStore/{store_name}", methods={"POST"})
     * @param $store_name
     * @return Response
     */
    public function insertStore($store_name): Response {
        $em = $this->getDoctrine()->getManager();

        $store = new Store();
        $store->setStoreName($store_name);

        $em->persist($store);

        $em->flush();

        return new Response('Store: Inserted Successfully');
    }

    /**
     * @Route("/insertProduct/{productName}/{productPrice}/store/{store_id}", methods={"POST"})
     * @param $productName
     * @param $productPrice
     * @param $store_id
     * @return Response
     */
    public function insertProduct($productName, $productPrice, $store_id): Response
    {
        if ($productName && $productPrice && $productPrice > 0) {
            $em = $this->getDoctrine()->getManager();
            $repository = $this->getDoctrine()
                ->getRepository(Store::class);

            $store = $repository->findOneBy(array('id' => $store_id));

            $product = new Product();
            $product->setProductDescr($productName);
            $product->setProductPrice($productPrice);
            $product->setStore($store);

            $em->persist($product);

            $em->flush();

            return new Response('Product: Inserted Successfully.');
        }
    }

    /**
     * @Route("/updateProduct/{product_id}/{product_price}", methods={"POST"})
     * @param $product_id
     * @param $product_price
     * @return Response
     */
    public function updateProduct($product_id, $product_price) {
        if ($product_id && $product_price && $product_price > 0) {
            $em = $this->getDoctrine()->getManager();
            $repository = $this->getDoctrine()
                    ->getRepository(Product::class);

            $product = $repository->findOneBy(array('id' => $product_id));

            if(!$product) {
                throw $this->createNotFoundException(
                    'Product with such id ' . $product_id . 'not found'
                );
            }

            $product->setProductPrice($product_price);

            $em->flush();

            return new JsonResponse('Product updated Successfully.');
        }
    }

    /**
     * @Route("/deleteProduct/{product_id}", methods={"DELETE"})
     * @param $product_id
     * @return Response
     */
    public function deleteProduct($product_id) {
        if ($product_id) {
            $em = $this->getDoctrine()->getManager();
            $repository = $this->getDoctrine()
                    ->getRepository();

            $product = $repository->findOneBy(array('id' => $product_id));

            if (!$product) {
                throw $this->createNotFoundException(
                    'Product with such id ' . $product_id . 'not found'
                );
            }

            $em->remove($product);

            $em->flush();

            return new Response('Product removed Successfully.');
        }
    }
}