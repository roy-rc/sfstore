<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Customer;
use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create Admin User
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setEmail('admin@sfstore.com');
        $admin->setRoles(['ROLE_ADMIN']);
        
        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            'admin123'  // Contraseña: admin123
        );
        $admin->setPassword($hashedPassword);
        $admin->setCreatedAt(new \DateTimeImmutable());
        
        $manager->persist($admin);

        // Create Categories
        $categories = [
            ['name' => 'Electrónicos', 'description' => 'Dispositivos electrónicos y gadgets'],
            ['name' => 'Ropa', 'description' => 'Vestimenta para hombres y mujeres'],
            ['name' => 'Hogar', 'description' => 'Artículos para el hogar y decoración'],
            ['name' => 'Deportes', 'description' => 'Equipamiento deportivo y fitness'],
            ['name' => 'Libros', 'description' => 'Libros físicos y digitales'],
        ];

        $categoryEntities = [];
        foreach ($categories as $categoryData) {
            $category = new Category();
            $category->setName($categoryData['name']);
            $category->setDescription($categoryData['description']);
            $category->setSlug(strtolower(str_replace(' ', '-', $categoryData['name'])));
            $category->setCreatedAt(new \DateTimeImmutable());
            
            $manager->persist($category);
            $categoryEntities[] = $category;
        }

        // Create Products
        $products = [
            [
                'name' => 'iPhone 15 Pro',
                'description' => 'El smartphone más avanzado de Apple',
                'price' => 999.99,
                'stock' => 50,
                'category' => 0, // Electrónicos
                'image' => 'https://via.placeholder.com/300x300/007bff/fff?text=iPhone+15'
            ],
            [
                'name' => 'Samsung Galaxy S24',
                'description' => 'Smartphone Android con cámara profesional',
                'price' => 899.99,
                'stock' => 30,
                'category' => 0, // Electrónicos
                'image' => 'https://via.placeholder.com/300x300/28a745/fff?text=Galaxy+S24'
            ],
            [
                'name' => 'MacBook Pro M3',
                'description' => 'Laptop profesional con chip M3 de Apple',
                'price' => 1999.99,
                'stock' => 15,
                'category' => 0, // Electrónicos
                'image' => 'https://via.placeholder.com/300x300/6c757d/fff?text=MacBook+Pro'
            ],
            [
                'name' => 'Camiseta Básica',
                'description' => 'Camiseta de algodón 100% en varios colores',
                'price' => 29.99,
                'stock' => 100,
                'category' => 1, // Ropa
                'image' => 'https://via.placeholder.com/300x300/17a2b8/fff?text=Camiseta'
            ],
            [
                'name' => 'Jeans Clásicos',
                'description' => 'Pantalones vaqueros de corte recto',
                'price' => 79.99,
                'stock' => 75,
                'category' => 1, // Ropa
                'image' => 'https://via.placeholder.com/300x300/343a40/fff?text=Jeans'
            ],
            [
                'name' => 'Sofá Moderno',
                'description' => 'Sofá de 3 plazas con diseño contemporáneo',
                'price' => 599.99,
                'stock' => 20,
                'category' => 2, // Hogar
                'image' => 'https://via.placeholder.com/300x300/fd7e14/fff?text=Sofa'
            ],
            [
                'name' => 'Lámpara de Mesa',
                'description' => 'Lámpara LED con regulador de intensidad',
                'price' => 49.99,
                'stock' => 40,
                'category' => 2, // Hogar
                'image' => 'https://via.placeholder.com/300x300/e83e8c/fff?text=Lampara'
            ],
            [
                'name' => 'Bicicleta Montaña',
                'description' => 'Bicicleta para trail con 21 velocidades',
                'price' => 449.99,
                'stock' => 25,
                'category' => 3, // Deportes
                'image' => 'https://via.placeholder.com/300x300/20c997/fff?text=Bicicleta'
            ],
            [
                'name' => 'Set de Pesas',
                'description' => 'Set completo de pesas para entrenamiento en casa',
                'price' => 199.99,
                'stock' => 30,
                'category' => 3, // Deportes
                'image' => 'https://via.placeholder.com/300x300/6f42c1/fff?text=Pesas'
            ],
            [
                'name' => 'El Quijote',
                'description' => 'La obra maestra de Miguel de Cervantes',
                'price' => 19.99,
                'stock' => 60,
                'category' => 4, // Libros
                'image' => 'https://via.placeholder.com/300x300/dc3545/fff?text=Quijote'
            ]
        ];

        foreach ($products as $index => $productData) {
            $product = new Product();
            $product->setName($productData['name']);
            $product->setDescription($productData['description']);
            $product->setPrice($productData['price']);
            $product->setStock($productData['stock']);
            $product->setSku('SKU' . str_pad($index + 1, 6, '0', STR_PAD_LEFT));
            $product->setSlug(strtolower(str_replace(' ', '-', $productData['name'])));
            $product->addCategory($categoryEntities[$productData['category']]);
            $product->setImage($productData['image']);
            $product->setCreatedAt(new \DateTimeImmutable());
            
            $manager->persist($product);
        }

        // Create Test Customer
        $customer = new Customer();
        $customer->setEmail('cliente@test.com');
        $customer->setFirstName('Juan');
        $customer->setLastName('Pérez');
        $customer->setPhone('+1234567890');
        $customer->setAddress('Calle Falsa 123');
        $customer->setDocumentNumber('12345678A');
        
        $customerPassword = $this->passwordHasher->hashPassword(
            $customer,
            'cliente123'  // Contraseña: cliente123
        );
        $customer->setPassword($customerPassword);
        $customer->setRoles(['ROLE_CUSTOMER']);
        $customer->setCreatedAt(new \DateTimeImmutable());
        
        $manager->persist($customer);
        
        $customerPassword = $this->passwordHasher->hashPassword(
            $customer,
            'cliente123'  // Contraseña: cliente123
        );
        $customer->setPassword($customerPassword);
        $customer->setRoles(['ROLE_CUSTOMER']);
        $customer->setCreatedAt(new \DateTimeImmutable());
        
        $manager->persist($customer);

        $manager->flush();
    }
}
