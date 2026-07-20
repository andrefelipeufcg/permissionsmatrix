<div align="right">
  🇬🇧 <a href="#english">English</a> | 🇫🇷 <a href="#français">Français</a> | 🇪🇸 <a href="#español">Español</a> | 🇧🇷 <a href="#português">Português</a> 
</div>

<a id="english"></a>
# GLPI Plugin - Permissions Matrix

A native GLPI plugin (compatible with versions 10 and 11) that generates a quick and exportable visualization of user permissions, crossing Profiles and Groups according to the selected Entities.

Developed to facilitate access auditing and the extraction of structured reports.

## ✨ Features

* **Visual Matrix Generation:** Dynamic table that displays active/inactive users and marks their respective profiles and groups with an "X".
* **Dynamic Visual Filters:** Ability to hide/show specific columns (profiles and groups) directly on the screen.
* **Native Access Control (RBAC):** Natively integrated into the GLPI Profiles screen, allowing you to define exactly who has the right to view the tool.
* **Smart Entity Filter:** Selection fields integrated with the native GLPI Select2 API. When selecting the profile entity, the group entity is automatically synchronized.
* **Advanced UX (Sticky Columns):** Dynamic freezing of the header row and user identification columns (Active, User, First name, Last name), allowing scrolling through extensive matrices without losing reference.
* **CSV Export:** Download the generated matrix in `.csv` format (UTF-8 encoding) with a single click, ready to be opened in Excel or spreadsheets.
* **Compatibility and Security:** Fully adapted for the GLPI 11 engine, using strict PHP 8 typing (`: bool`) and the new session token system (`_glpi_csrf_token`).

## 📋 Prerequisites

* **GLPI:** Version 10.0.0 or higher.
* **PHP:** Version 8.0 or higher.
* Web server access (SSH terminal) for permissions adjustment.

## 🚀 How to Install

1. **Download the plugin** and extract the files.
2. **Rename the folder** strictly to `permissionsmatrix` (no special characters or underscores, as required by GLPI).
3. Upload the folder to your GLPI server's plugin directory:
   ```bash
   /var/www/your_glpi/plugins/permissionsmatrix
   ```

4. **Adjust server permissions (Important):**
   The web server needs read permission to compile the Autoloader. Access your terminal and execute:
   ```bash
   sudo chown -R www-data:www-data /var/www/your_glpi/plugins/permissionsmatrix
   sudo chmod -R 755 /var/www/your_glpi/plugins/permissionsmatrix
   ```
   *(Note: If your server uses CentOS/RedHat, the user might be `apache` instead of `www-data`).*

5. **Clear the Cache (To ensure class reading in GLPI 11):**
   ```bash
   sudo -u www-data php /var/www/your_glpi/bin/console cache:clear
   sudo systemctl restart apache2
   ```

6. **Activate in GLPI:**
   * Log in with the Super-Admin profile.
   * Navigate to **Setup > Plugins**.
   * Find "Permissions Matrix", click **Install**, and then **Enable**.

## 🔒 Access Control (Permissions)

**Important:** By default, right after installation, **only the Super-Admin profile** is automatically granted permission to view the Permissions Matrix (this is a mandatory security requirement from the GLPI Marketplace). 

If you want another specific profile (e.g. admin, observer) to have access to the tool:
1. Navigate to **Administration > Profiles**.
2. Click on the profile you want to grant access.
3. On the side menu, click the **Permissions Matrix** tab.
4. Change the option to **Yes** and click Save. 
*(The "Tools > Permissions Matrix" menu will now be displayed for users with that profile).*

## 📖 How to Use

1. In the GLPI menu, go to **Tools > Permissions Matrix**.
2. Select the desired entity in the available fields.
3. Click **Generate Permissions Matrix**.
4. Use the **Hide/Show Columns (Visual Filter)** button to refine the table on the screen, if necessary.
5. View the data on the screen or click **Export to CSV** to download it.

## 🛠️ Directory Structure

* `setup.php` and `hook.php`: Initialization and hook registrations in the GLPI ecosystem, including the initial injection of permissions into the database.
* `inc/matriz.class.php`: Main control class and top menu rendering.
* `inc/profile.class.php`: Injection of the configuration tab into the native Profiles screen.
* `front/matriz.php`: Main generator visual interface (entity selection).
* `front/processa_matriz.php`: Database search engine, HTML table generation with advanced UX, and CSV export.
* `front/profile.form.php`: Permissions saving processor.

## 📄 License

This project is licensed under the GPLv2+ license, following the standard of the GLPI framework.

---

<a id="français"></a>
# Plugin GLPI - Matrice de Permissions

Un plugin natif pour GLPI (compatible avec les versions 10 et 11) qui génère une visualisation rapide et exportable des permissions des utilisateurs, en croisant les Profils et les Groupes selon les Entités sélectionnées.

Développé pour faciliter l'audit des accès et l'extraction de rapports structurés.

## ✨ Fonctionnalités

* **Génération de Matrice Visuelle :** Tableau dynamique qui affiche les utilisateurs actifs/inactifs et marque avec un "X" leurs profils et groupes respectifs.
* **Filtres Visuels Dynamiques :** Possibilité de masquer/afficher des colonnes spécifiques (profils et groupes) directement à l'écran.
* **Contrôle d'Accès Natif (RBAC) :** Intégré nativement à l'écran des Profils de GLPI, permettant de définir exactement qui a le droit de visualiser l'outil.
* **Filtre Intelligent par Entité :** Champs de sélection intégrés avec l'API Select2 native de GLPI. Lors de la sélection de l'entité du profil, l'entité du groupe est automatiquement synchronisée.
* **UX Avancée (Colonnes figées) :** Figeage dynamique de la ligne d'en-tête et des colonnes d'identification de l'utilisateur (Actif, Utilisateur, Prénom, Nom), permettant de faire défiler de longues matrices sans perdre la référence.
* **Exportation CSV :** Téléchargement de la matrice générée au format `.csv` (encodage UTF-8) en un seul clic, prête à être ouverte dans Excel ou d'autres tableurs.
* **Compatibilité et Sécurité :** Totalement adapté au moteur de GLPI 11, utilisant le typage strict de PHP 8 (`: bool`) et le nouveau système de jetons de session (`_glpi_csrf_token`).

## 📋 Prérequis

* **GLPI :** Version 10.0.0 ou supérieure.
* **PHP :** Version 8.0 ou supérieure.
* Accès au serveur web (terminal SSH) pour l'ajustement des permissions.

## 🚀 Comment l'installer

1. **Téléchargez le plugin** et extrayez les fichiers.
2. **Renommez le dossier** obligatoirement en `permissionsmatrix` (sans caractères spéciaux ni tirets bas, comme exigé par GLPI).
3. Envoyez le dossier dans le répertoire des plugins de votre serveur GLPI :
   ```bash
   /var/www/votre_glpi/plugins/permissionsmatrix
   ```

4. **Ajustez les permissions sur le serveur (Important) :**
   Le serveur web doit avoir la permission de lecture pour compiler l'Autoloader. Accédez à votre terminal et exécutez :
   ```bash
   sudo chown -R www-data:www-data /var/www/votre_glpi/plugins/permissionsmatrix
   sudo chmod -R 755 /var/www/votre_glpi/plugins/permissionsmatrix
   ```
   *(Remarque : Si votre serveur utilise CentOS/RedHat, l'utilisateur peut être `apache` au lieu de `www-data`).*

5. **Videz le cache (Pour garantir la lecture de la classe dans GLPI 11) :**
   ```bash
   sudo -u www-data php /var/www/votre_glpi/bin/console cache:clear
   sudo systemctl restart apache2
   ```

6. **Activez dans GLPI :**
   * Connectez-vous avec le profil Super-Admin.
   * Allez dans **Configuration > Plugins**.
   * Trouvez "Matrice de Permissions", cliquez sur **Installer**, puis sur **Activer**.

## 🔒 Contrôle d'Accès (Permissions)

**Important :** Par défaut, juste après l'installation, **seul le profil Super-Admin** reçoit automatiquement la permission de visualiser la Matrice de Permissions (il s'agit d'une exigence de sécurité obligatoire du Marketplace GLPI). 

Si vous souhaitez qu'un autre profil spécifique (par exemple, admin, observateur) ait accès à l'outil :
1. Allez dans **Administration > Profils**.
2. Cliquez sur le profil auquel vous souhaitez accorder l'accès.
3. Dans le menu latéral, cliquez sur l'onglet **Matrice de Permissions**.
4. Modifiez l'option sur **Oui** et cliquez sur Sauvegarder. 
*(Le menu "Outils > Matrice de Permissions" sera désormais affiché pour les utilisateurs de ce profil).*

## 📖 Comment l'utiliser

1. Dans le menu de GLPI, allez dans **Outils > Matrice de Permissions**.
2. Sélectionnez l'entité souhaitée dans les champs disponibles.
3. Cliquez sur **Générer la Matrice de Permissions**.
4. Utilisez le bouton **Masquer/Afficher les Colonnes (Filtre Visuel)** pour affiner le tableau à l'écran, si nécessaire.
5. Visualisez les données à l'écran ou cliquez sur **Exporter en CSV** pour les télécharger.

## 🛠️ Structure des Répertoires

* `setup.php` et `hook.php` : Initialisation et enregistrement des hooks dans l'écosystème GLPI, y compris l'injection initiale des permissions dans la base de données.
* `inc/matriz.class.php` : Classe de contrôle principale et rendu du menu supérieur.
* `inc/profile.class.php` : Injection de l'onglet de configuration dans l'écran natif des Profils.
* `front/matriz.php` : Interface visuelle principale du générateur (sélection des entités).
* `front/processa_matriz.php` : Moteur de recherche dans la base de données, génération du tableau HTML avec une UX avancée et exportation CSV.
* `front/profile.form.php` : Processeur de sauvegarde des permissions.

## 📄 Licence

Ce projet est sous licence GPLv2+, suivant le standard du framework GLPI.

---

<a id="español"></a>
# GLPI Plugin - Permissions Matrix (Matriz de Permisos)

Un plugin nativo de GLPI (compatible con las versiones 10 y 11) que genera una visualización rápida y exportable de los permisos de los usuarios, cruzando Perfiles y Grupos según las Entidades seleccionadas.

Desarrollado para facilitar la auditoría de accesos y la extracción de informes estructurados.

## ✨ Características

* **Generación Visual de Matriz:** Tabla dinámica que muestra usuarios activos/inactivos y marca sus respectivos perfiles y grupos con una "X".
* **Filtros Visuales Dinámicos:** Posibilidad de ocultar/mostrar columnas específicas (perfiles y grupos) directamente en la pantalla.
* **Control de Acceso Nativo (RBAC):** Integrado de forma nativa en la pantalla de Perfiles de GLPI, lo que permite definir exactamente quién tiene derecho a ver la herramienta.
* **Filtro de Entidades Inteligente:** Campos de selección integrados con la API nativa Select2 de GLPI. Al seleccionar la entidad del perfil, la entidad del grupo se sincroniza automáticamente.
* **UX Avanzada (Columnas Adhesivas):** Congelación dinámica de la fila de encabezado y de las columnas de identificación del usuario (Activo, Usuario, Nombre, Apellido), lo que permite desplazarse por matrices extensas sin perder la referencia.
* **Exportación CSV:** Descargue la matriz generada en formato `.csv` (codificación UTF-8) con un solo clic, lista para abrirse en Excel o hojas de cálculo.
* **Compatibilidad y Seguridad:** Totalmente adaptado para el motor GLPI 11, utilizando el tipado estricto de PHP 8 (`: bool`) y el nuevo sistema de tokens de sesión (`_glpi_csrf_token`).

## 📋 Requisitos Previos

* **GLPI:** Versión 10.0.0 o superior.
* **PHP:** Versión 8.0 o superior.
* Acceso al servidor web (terminal SSH) para ajuste de permisos.

## 🚀 Cómo Instalar

1. **Descargue el plugin** y extraiga los archivos.
2. **Renombre la carpeta** estrictamente a `permissionsmatrix` (sin caracteres especiales ni guiones bajos, como lo requiere GLPI).
3. Suba la carpeta al directorio de plugins de su servidor GLPI:
   ```bash
   /var/www/su_glpi/plugins/permissionsmatrix
   ```

4. **Ajuste los permisos en el servidor (Importante):**
   El servidor web necesita permisos de lectura para compilar el Autoloader. Acceda a su terminal y ejecute:
   ```bash
   sudo chown -R www-data:www-data /var/www/su_glpi/plugins/permissionsmatrix
   sudo chmod -R 755 /var/www/su_glpi/plugins/permissionsmatrix
   ```
   *(Nota: Si su servidor utiliza CentOS/RedHat, el usuario podría ser `apache` en lugar de `www-data`).*

5. **Limpie la Caché (Para garantizar la lectura de clases en GLPI 11):**
   ```bash
   sudo -u www-data php /var/www/su_glpi/bin/console cache:clear
   sudo systemctl restart apache2
   ```

6. **Actívelo en GLPI:**
   * Inicie sesión con el perfil de Super-Admin.
   * Navegue hasta **Configuración > Complementos**.
   * Busque "Matriz de Permisos", haga clic en **Instalar** y luego en **Activar**.

## 🔒 Control de Acceso (Permisos)

**Importante:** Por defecto, justo después de la instalación, **solo el perfil Super-Admin** recibe automáticamente permiso para ver la Matriz de Permisos (esto es un requisito de seguridad obligatorio del GLPI Marketplace). 

Si desea que otro perfil específico (ej. admin, observador) tenga acceso a la herramienta:
1. Navegue hasta **Administración > Perfiles**.
2. Haga clic en el perfil al que desea conceder acceso.
3. En el menú lateral, haga clic en la pestaña **Matriz de Permisos**.
4. Cambie la opción a **Sí** y haga clic en Guardar. 
*(El menú "Herramientas > Matriz de Permisos" ahora se mostrará a los usuarios con ese perfil).*

## 📖 Cómo Usar

1. En el menú de GLPI, vaya a **Herramientas > Matriz de Permisos**.
2. Seleccione la entidad deseada en los campos disponibles.
3. Haga clic en **Generar Matriz de Permisos**.
4. Utilice el botón **Ocultar/Mostrar Columnas (Filtro Visual)** para refinar la tabla en la pantalla, si es necesario.
5. Vea los datos en la pantalla o haga clic en **Exportar a CSV** para descargarlos.

## 🛠️ Estructura de Directorios

* `setup.php` y `hook.php`: Inicialización y registros de hooks en el ecosistema de GLPI, incluyendo la inyección inicial de permisos en la base de datos.
* `inc/matriz.class.php`: Clase de control principal y renderizado del menú superior.
* `inc/profile.class.php`: Inyección de la pestaña de configuración en la pantalla nativa de Perfiles.
* `front/matriz.php`: Interfaz visual del generador principal (selección de entidades).
* `front/processa_matriz.php`: Motor de búsqueda en base de datos, generación de tabla HTML con UX avanzada y exportación CSV.
* `front/profile.form.php`: Procesador de guardado de permisos.

## 📄 Licencia

Este proyecto está licenciado bajo la licencia GPLv2+, siguiendo el estándar del marco GLPI.

---

<a id="português"></a>
# Plugin GLPI - Matriz de Permissões

Um plugin nativo para o GLPI (compatível com as versões 10 e 11) que gera uma visualização rápida e exportável das permissões dos usuários, cruzando Perfis e Grupos de acordo com as Entidades selecionadas.

Desenvolvido para facilitar a auditoria de acessos e a extração de relatórios estruturados.

## ✨ Funcionalidades

* **Geração de Matriz Visual:** Tabela dinâmica que exibe os usuários ativos/inativos e marca com um "X" os seus respectivos perfis e grupos.
* **Filtros Visuais Dinâmicos:** Possibilidade de ocultar/mostrar colunas específicas (perfis e grupos) diretamente na tela.
* **Controle de Acesso Nativo (RBAC):** Integrado nativamente à tela de Perfis do GLPI, permitindo definir exatamente quem tem o direito de visualizar a ferramenta.
* **Filtro Inteligente por Entidade:** Campos de seleção integrados com a API do Select2 nativa do GLPI. Ao selecionar a entidade do perfil, a entidade do grupo é sincronizada automaticamente.
* **UX Avançada (Sticky Columns):** Congelamento dinâmico da linha de cabeçalho e das colunas de identificação do usuário (Ativo, Usuário, Nome, Sobrenome), permitindo rolar matrizes extensas sem perder a referência.
* **Exportação para CSV:** Download da matriz gerada em formato `.csv` (codificação UTF-8) com um único clique, pronta para ser aberta no Excel ou planilhas.
* **Compatibilidade e Segurança:** Totalmente adaptado para o motor do GLPI 11, utilizando tipagem estrita do PHP 8 (`: bool`) e o novo sistema de tokens de sessão (`_glpi_csrf_token`).

## 📋 Pré-requisitos

* **GLPI:** Versão 10.0.0 ou superior.
* **PHP:** Versão 8.0 ou superior.
* Acesso ao servidor web (terminal SSH) para ajuste de permissões.

## 🚀 Como Instalar

1. **Faça o download do plugin** e extraia os arquivos.
2. **Renomeie a pasta** obrigatoriamente para `permissionsmatrix` (sem caracteres especiais ou sublinhados, exigência do GLPI).
3. Envie a pasta para o diretório de plugins do seu servidor GLPI:
   ```bash
   /var/www/seu_glpi/plugins/permissionsmatrix
   ```

4. **Ajuste as permissões no servidor (Importante):**
   O servidor web precisa ter permissão de leitura para compilar o Autoloader. Acesse seu terminal e execute:
   ```bash
   sudo chown -R www-data:www-data /var/www/seu_glpi/plugins/permissionsmatrix
   sudo chmod -R 755 /var/www/seu_glpi/plugins/permissionsmatrix
   ```
   *(Nota: Se o seu servidor utilizar CentOS/RedHat, o usuário pode ser o `apache` em vez de `www-data`).*

5. **Limpe o Cache (Para garantir a leitura da classe no GLPI 11):**
   ```bash
   sudo -u www-data php /var/www/seu_glpi/bin/console cache:clear
   sudo systemctl restart apache2
   ```

6. **Ative no GLPI:**
   * Acesse o sistema com o perfil de Super-Admin.
   * Navegue até **Configurar > Plugins**.
   * Localize o "Matriz de Permissões", clique em **Instalar** e, em seguida, em **Habilitar**.

## 🔒 Controle de Acesso (Permissões)

**Importante:** Por padrão, logo após a instalação, **apenas o perfil Super-Admin** recebe permissão automática para visualizar a Matriz de Permissões (esse é um requisito obrigatório de segurança do Marketplace do GLPI). 

Caso deseje que algum outro perfil específico (ex: admin, observador) tenha acesso à ferramenta:
1. Navegue até **Administração > Perfis**.
2. Clique no perfil que deseja conceder acesso.
3. No menu lateral, clique na aba **Matriz de Permissões**.
4. Altere a opção para **Sim** e clique em Salvar. 
*(O menu "Ferramentas > Matriz de Permissões" passará a ser exibido para os usuários daquele perfil).*

## 📖 Como Usar

1. No menu do GLPI, vá em **Ferramentas > Matriz de Permissões**.
2. Selecione a entidade desejada nos campos disponíveis.
3. Clique em **Gerar Matriz de Permissões**.
4. Utilize o botão **Ocultar/Mostrar Colunas (Filtro Visual)** para refinar a tabela na tela, se necessário.
5. Visualize os dados em tela ou clique em **Exportar para CSV** para fazer o download.

## 🛠️ Estrutura de Diretórios

* `setup.php` e `hook.php`: Inicialização e registros de hooks no ecossistema do GLPI, incluindo a injeção inicial de permissões no banco de dados.
* `inc/matriz.class.php`: Classe de controle principal e renderização do menu superior.
* `inc/profile.class.php`: Injeção da aba de configuração na tela nativa de Perfis.
* `front/matriz.php`: Interface visual do gerador principal (seleção de entidades).
* `front/processa_matriz.php`: Motor de busca no banco de dados, geração da tabela HTML com UX avançada e exportação CSV.
* `front/profile.form.php`: Processador de salvamento das permissões.

## 📄 Licença

Este projeto está licenciado sob a licença GPLv2+, seguindo o padrão do framework GLPI.

---