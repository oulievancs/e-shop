<?php


namespace App\Controller;


use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\User;
use Exception;
use Proxies\__CG__\App\Entity\Store;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;

class ContentController extends AbstractController
{
    /**
     * @Route("/getProducts/{store_id}")
     * @param $store_id
     * @return Exception|JsonResponse|Response
     */
    public function getProducts($store_id) {
        $session = new Session(new NativeSessionStorage(), new AttributeBag());
        if ($session) {
            if ($store_id) {
                $repository = $this->getDoctrine()
                    ->getRepository(Product::class);

                $products = $repository->findBy(array('Store' => $store_id));

                return new JsonResponse($products);
            } else {
                return new Exception('You must provide a store identity.');
            }
        } else {
            return new Response('Not Allowed!', 403);
        }
    }

    /**
     * @Route("/getAllProducts", methods={"GET"})
     * @return JsonResponse
     */
    public function getAllProducts() {
        $session = new Session(new NativeSessionStorage(), new AttributeBag());
        if ($session) {
            $repository = $this->getDoctrine()
                ->getRepository(Product::class);

            $products = $repository->findAll();

            return new JsonResponse($products);
        } else {
            return new Response('Not Allowed!', 403);
        }
    }

    /**
     * @Route("/getStores")
     * @return Response
     */
    public function getStores() {
        $session = new Session(new NativeSessionStorage(), new AttributeBag());
        if($session && $session->get('role')) {
            $repository = $this->getDoctrine()
                ->getRepository(Store::class);

            $stores = $repository->findAll();

            return new JsonResponse($stores);
        } else {
            return new Response('Not Allowed!', 403);
        }
    }

    /**
     * @Route("/insertStore/{store_name}", methods={"POST"})
     * @param $store_name
     * @return Response
     */
    public function insertStore($store_name): Response {
        $session = new Session(new NativeSessionStorage(), new AttributeBag());
        if ($session && $session->get('role')=='admin') {
            $em = $this->getDoctrine()->getManager();

            $store = new Store();
            $store->setStoreName($store_name);

            $em->persist($store);

            $em->flush();

            return new Response('Store: Inserted Successfully');
        } else {
            return new Response('Not Allowed!', 403);
        }
    }

    /**
     * @Route("/insertProduct/{productName}/{productPrice}/store/{store_id}", methods={"POST"})
     * @param $productName
     * @param $productPrice
     * @param $store_id
     * @return Response
     */
    public function insertProduct($productName, $productPrice, $store_id): Response {
        $session = new Session(new NativeSessionStorage(), new AttributeBag());
        if ($session && $session->get('role')=='admin') {
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
        } else {
            return new Response('Not Allowed!', 403);
        }
    }

    /**
     * @Route("/updateProduct/{product_id}/{productPrice}", methods={"POST"})
     * @param $product_id
     * @param productPrice
     * @return Response
     */
    public function updateProduct($product_id, $productPrice) {
        $session = new Session(new NativeSessionStorage(), new AttributeBag());
        if ($session && $session->get('role')=='admin') {
            if ($product_id && $productPrice && $productPrice > 0) {
                $em = $this->getDoctrine()->getManager();
                $repository = $this->getDoctrine()
                    ->getRepository(Product::class);

                $product = $repository->findOneBy(array('id' => $product_id));

                if (!$product) {
                    throw $this->createNotFoundException(
                        'Product with such id ' . $product_id . 'not found'
                    );
                }

                $product->setProductPrice($productPrice);

                $em->flush();

                return new JsonResponse('Product updated Successfully.');
            }
        } else {
            return new Response('Not Allowed!', 403);
        }
    }

    /**
     * @Route("/deleteProduct/{product_id}", methods={"DELETE"})
     * @param $product_id
     * @return Response
     */
    public function deleteProduct($product_id) {
        $session = new Session(new NativeSessionStorage(), new AttributeBag());
        if ($session && $session->get('role')=='admin') {
            if ($product_id) {
                $em = $this->getDoctrine()->getManager();
                $repository = $this->getDoctrine()
                    ->getRepository(Product::class);

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
        } else {
            return new Response('Not Allowed!', 403);
        }
    }

    /**
     * @Route("/login/{username}/{password}", methods={"POST"})
     * @param $username
     * @param $password
     * @return JsonResponse
     */
    public function login($username, $password) {
        if ($username && $password) {
            $repository = $this->getDoctrine()
                        ->getRepository(User::class);

            $user = $repository->findOneBy(array('Username' => $username));

            if ($user && $user->getPassword() == $password) {
                $session = new Session(new NativeSessionStorage(), new AttributeBag());
                $session->start();

                $session->set('role', 'admin');
                $session->set('username', $username);

                return new JsonResponse(array('role' => 'admin', 'valid' =>true));
            } else {
                $repository = $this->getDoctrine()
                        ->getRepository(Customer::class);

                $customer = $repository->findOneBy(array('Username' => $username));

                if ($customer && $customer->getPassword() == $password) {
                    $session = new Session(new NativeSessionStorage(), new AttributeBag());
                    $session->start();

                    $session->set('role', 'customer');
                    $session->set('username', $username);

                    return new JsonResponse(array('role' => 'customer', 'valid' => true));
                } else {
                    return new JsonResponse(array('valid' => false));
                }
            }
        } else {
            return new JsonResponse(array('valid' => false));
        }
    }

    /**
     * @Route("/login", methods={"GET"})
     * @return JsonResponse
     */
    public function login1() {
        $session = new Session(new NativeSessionStorage(), new AttributeBag());
        if ($session && $session->get('role')) {
            return new JsonResponse(array('role' => $session->get('role'), 'valid' => true, 'username' => $session->get('username')));
        } else {
            return new JsonResponse(array('valid' => false));
        }
    }

    /**
     * @Route("/logout", methods={"POST"})
     * @return Response
     */
    public function logout() {
        $session = new Session(new NativeSessionStorage(), new AttributeBag());

        if ($session) {
            $session->clear();

            return new Response('Seccessfully loging out.');
        } else {
            return new Response('Not Allowed!', 403);
        }
    }
}