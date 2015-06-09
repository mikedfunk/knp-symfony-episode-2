These are notes from [KNP University: Starting in Symfony2: episode 2](https://knpuniversity.com/screencast/symfony2-ep2/). This repo and the notes below are pretty messy but I'm keeping it around for later reference.

# KNP University notes

* auth is all done in `app/config/security.yml` which is included from app/config/config.yml
* the *firewall* is all about finding out who you are (authentication), not whether you can do something (authorization)
* *access_control* is about authorization (whether you can do something).
* `is_granted('IS_AUTHENTICATE_REMEMBERED') // logged in or remembered with cookie`
* `is_granted('IS_AUTHENTICATE_FULLY') // logged in this session, not with a remember me cookie`
* `is_granted('IS_AUTHENTICATE_ANONYMOUSLY') // not logged in`
* translation is by this file naming convention: `domain.locale.format` e.g. `security.en.yml`. You can put it in `app/Resources/translations/`. You can also put it in a bundle but make sure the one that takes precedence is loaded after any previous ones of the same domain.
* bcrypt default is 13 cost
* dependency injection online will often say to use `$this->get(...)` or `$this->container(...)`. Don't do that! All you do is define your controller as a service in `app/config/services.yml` and define any dependencies that get injected into the constructor. Then if you're using annotation-based routing, put this docblock above your class declaration: `@Route(service="my_service_key")`. Now your controller will instantiate using the service container (dependency injection container) and pass things to the constructor.
* when sending the results of a form builder to the view, you must do $form = `$formBuilder->createView()` in the controller.
* `form_row(form.my_field)` just wraps the row in a div, then calls `form_errors(form.my_field)`, `form_label(form.my_field)`, and `form_widget(form.my_field)`. You can use these separately for customization.
* don't forget to call `form_errors(form)` on the entire form object at the top or wherever.
* at the end call `form_rest(form)` which renders any fields you forgot. It also renders any hidden fields automatically including the csrf token.
* for form builders, you can pass a `data_class` key to tell it to return a populated object when it passes validation:
```php
$data = ['data_class' => 'UserBundle\Entity\User'];
return $this->createFormBuilder(null, $data)
    ->add('username', 'text')
    ->add('email', 'email')
    ->add('password', 'repeated', [
        'type' => 'password'
    ])
    ->getForm();
```
* form types (form primitive objects) are typically defined in separate files, then created from the controller. The form type is usually under `WhateverBundle/Form/WhateverFormType.php`. It extends `AbstractType` and has at least the `getName()` method and the `buildForm()` method to assign form elements:
```php

    /**
     * get name
     *
     * @return string
     */
    public function getName()
    {
        return 'user_register';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        // define the fields
        return $builder->add('username', 'text')
            ->add('email', 'email')
            ->add('plainPassword', 'repeated', [
                'type' => 'password'
            ]);
    }
```
* It can also have the `setDefaultOptions()` method to define which `data_class` to instantiate and hydrate when the form is submitted:
```php

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $settings = ['data_class' => 'UserBundle\Entity\User'];
        $resolver->setDefaults($settings);
        $this->configureOptions($resolver);
    }
```
* form fields default to html5 required! You can turn this off in the form type class for each field. Set the third param to an array, with `['required' => false]`.
* you can disable all html5 validation in the `form_start()` function like so:
```
{{ form_start(form, {'attr': {'novalidate': 'novalidate'}}) }}
```
* to validate an orm entity from a form, first you MUST pass an ORM entity when calling `createForm()` from the controller. e.g.:
```php

    /**
     * create register form
     *
     * @return Symfony\Component\Form\Form
     */
    private function getRegisterForm()
    {
        // defaults. this is crucial to pass to createForm() if you are using
        // entity annotation validation. It can be empty, you don't need to set
        // defaults.
        $user = new User();
        $user->setUsername('Joe');
        return $this->createForm(new RegisterFormType(), $user);
    }
```
* Next, add Assert to the use statements: `use Symfony\Component\Validator\Constraints as Assert;`
* Finally, you need to define an assertion annotation on an entity class property:
```php

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255)
     * @Assert\NotBlank(message="Gimme a username")
     */
    private $username;
```

* you can redirect to a route with `return $this->redirectToRoute('route_name');` in the controller. This returns a new RedirectResponse with the url and code.
* checking for unique entities (no duplicate usernames) is special. It's a separate use statement: `use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;`. Then you do `@UniqueEntity` on the *class*, not the method:
```php
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="UserBundle\Entity\UserRepository")
 * @UniqueEntity(fields="email", message="That email is taken dude")
 * @UniqueEntity(fields="username", message="That username is taken dude")
 */
class User implements AdvancedUserInterface, \Serializable
```
* There is also a [Callback](http://symfony.com/doc/current/reference/constraints/Callback.html) validation option to let you use your own logic to validate a value. There you go.
* You can type hint the Request object to get an auto-populated http foundation request in a controller method. This is the only object you can do this with, although you can coerce data to be a certain type. That's a different thing. E.g. of request thing:
```php

    /**
     * post to register
     *
     * @Route("/register", name="user_do_register")
     * @Method("POST")
     * @Template("UserBundle:register:register.html.twig")
     * @return Response|array
     */
    public function doRegisterAction(Request $request)
    {
```
* flash messages! `$request->getSession()->getFlashbag()->add('success', 'A winner is you!');`
* and in the view:
```twig
{% if app.session.flashbag.has('success') %}
    <div class="alert alert-success">
        {% for message in app.session.flashbag.get('success') %}
            {{ message }}
        {% endfor %}
    </div>
{% endif %}
```
* how to manually log in as a user: (maybe there's a better way than this?)
```php

    /**
     * login the user
     *
     * @param User $user
     * @return void
     */
    private function authenticateUser(User $user)
    {
        $providerKey = 'secured_area'; // your firewall name
        $token = new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());
        $this->securityContext->setToken($token);
    }
```
* on defining a FormType, leave the second attr of a field null to let Symfony's field guessing kick in. This will try to guess required, length, and pattern for you based on database values. She recommends trying it at first and explicitly set things that aren't guessed correctly. e.g.:
```php
// in the User entity...

    /**
     * email address
     *
     * @var string
     *
     * since nullable is false, it will guess that this field is required.
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    protected $email;
```

```php
// in the RegisterFormType...

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        // define the fields
        return $builder->add('username', 'text')
            
            // no field type guessing
            // ->add('email', 'email')
            
            // field type guessing! set second arg to null
            ->add('email', null)
            ->add('plainPassword', 'repeated', [
                'type' => 'password'
            ]);
    }
```
* important gotcha on this: if you don't let symfony guess the field type it won't guess any of the other options either such as max length.
* she recommends defining roles like actions instead of roles, then defining the role hierarchy in the security.yml. Lame.
* there is a built-in feature attached to roles and an exact role to allow you to switch users and switch back. Cool and all but very specific.
* in twig you can access the user object with app.user: `{{ app.user.username }}`
* in the controller you can access the user object with `$this->container->get('security.context')->getToken()->getUser()`. Of course don't do `$this->container->get()`. Use DI instead!
* UPDATE: An easier way to do the above is `$this->getUser()` from the controller. Neat.
* activate *remember_me* functionality by adding this to the firewalls key in security.yml:
```yaml
# ...
    firewalls:
        my_firewall_name:
            # ...
            remember_me:
                key: "my secret random key"
```
* then add a checkbox to your form with the name of `_remember_me`. Then symfony will take care of everything automatically.
```twig
Remember Me: <input type="checkbox" name="_remember_me" />
```
