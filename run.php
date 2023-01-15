<?php

use Github\AuthMethod;
use Github\Client;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttplugClient;

require_once __DIR__ . "/vendor/autoload.php";

if (file_exists(__DIR__ . "/.env")) {
    (new Dotenv())->usePutenv()->loadEnv(__DIR__. '/.env');
}

const ORG_NAME = "code4romania";

(new SingleCommandApplication())
    ->addArgument('report-name', InputArgument::OPTIONAL, "CSV report filename", 'repo-report.csv')
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);
        $client = Client::createWithHttpClient(new HttplugClient());

        $client->authenticate(getenv('GITHUB_TOKEN'), "", AuthMethod::ACCESS_TOKEN);
        $report = fopen("reports/" . $input->getArgument('report-name'), 'w+');

        fputcsv(
            $report,
            [
                'name',
                'url',
                'last_push',
                'has_readme',
                'section_developOrInstall',
                'section_techDocumentation',
                'section_contributors',
                'section_aboutAndDocs',
                'section_feedback',
                'section_licence',
                'section_aboutCode4Ro',
                'section_donate',
                'section_harassment',
                'raw_identified_sections',
            ]);

        $reposPage = 1;
        do {
            $repos = $client->organizations()->repositories(ORG_NAME, 'all', $reposPage);
            foreach($repos as $repo) {
                try {

                    $crawler = new Crawler(
                        $client->repo()->readme(ORG_NAME, $repo['name'], 'html')
                    );

                    $reportRow = [
                        'name' => $repo['full_name'],
                        'url' => $repo['html_url'],
                        'last_push' => $repo['pushed_at'],
                        'has_readme' => 'yes',
                        'section_developOrInstall' => "no",
                        'section_techDocumentation' => "no",
                        'section_contributors' => "no",
                        'section_aboutAndDocs' => "no",
                        'section_feedback' => "no",
                        'section_licence' => "no",
                        'section_aboutCode4Ro' => "no",
                        'section_donate' => "no",
                        'section_harassment' => "no",
                        'raw_identified_sections' => ""
                    ];

                    $rawSections = [];

                    $crawler
                        ->filter("h2")
                        ->reduce(function (Crawler $node, $i) use ($io, &$reportRow, &$rawSections, $repo) {
                            $rawSections[] = $node->text();

                            switch (strtolower($node->text())) {
                                case 'install':
                                case 'installation':
                                case 'instalare':
                                case 'creating the database --- wip you might encounter issues here.':
                                case 'develop':
                                case 'test data':
                                case 'deployment':
                                case 'development':
                                case 'requirements':
                                case 'instructions':
                                case 'testing out the api':
                                case 'configure your database':
                                case 'run the project':
                                case 'build':
                                case 'deployed in:':
                                case 'prerequisite tools':
                                case 'getting started':
                                case 'start virtual machine':
                                case 'restore the database':
                                case 'client deployment':
                                case 'setup':
                                case 'local deployment':
                                case 'build setup':
                                case 'development tips':
                                case 'set up':
                                case 'deployment on aws / microsoft azure with terraform':
                                case 'configuration for development and test environments':
                                case 'local development':
                                    $reportRow['section_developOrInstall'] = "yes";
                                    break;
                                case 'architecture':
                                case 'commands':
                                case 'localization & internationalization':
                                case 'utile':
                                case 'tech stuff':
                                case 'todos':
                                case 'project structure':
                                case 'main features':
                                case 'unelte':
                                case 'instituții publice':
                                case 'updating to new releases':
                                case 'folder structure':
                                case 'available scripts':
                                case 'supported browsers':
                                case 'supported language features and polyfills':
                                case 'syntax highlighting in the editor':
                                case 'displaying lint output in the editor':
                                case 'debugging in the editor':
                                case 'formatting code automatically':
                                case 'changing the page <title>':
                                case 'installing a dependency':
                                case 'importing a component':
                                case 'code splitting':
                                case 'adding a stylesheet':
                                case 'post-processing css':
                                case 'adding a css preprocessor (sass, less etc.)':
                                case 'adding images, fonts, and files':
                                case 'using the public folder':
                                case 'using global variables':
                                case 'adding bootstrap':
                                case 'adding flow':
                                case 'adding a router':
                                case 'adding custom environment variables':
                                case 'can i use decorators?':
                                case 'fetching data with ajax requests':
                                case 'integrating with an api backend':
                                case 'proxying api requests in development':
                                case 'using https in development':
                                case 'generating dynamic <meta> tags on the server':
                                case 'pre-rendering into static html files':
                                case 'injecting data from the server into the page':
                                case 'running tests':
                                case 'debugging tests':
                                case 'developing components in isolation':
                                case 'publishing components to npm':
                                case 'making a progressive web app':
                                case 'analyzing the bundle size':
                                case 'advanced configuration':
                                case 'troubleshooting':
                                case 'alternatives to ejecting':
                                case 'lint your code before you commit!':
                                case 'debug with typescript and vscode':
                                case 'app structure':
                                case 'built with':
                                case 'documentation:':
                                case 'how it works':
                                case 'examples':
                                case 'debugging':
                                case 'translating content':
                                case 'swagger':
                                case 'management commands':
                                case 'testing':
                                case 'production':
                                case 'technologies used':
                                case 'development daily docker commands:':
                                case 'local debug and test with':
                                case 'old setup':
                                case 'live:':
                                case 'learn more':
                                case 'about laravel':
                                case 'learning laravel':
                                case 'security vulnerabilities':
                                case 'usage':
                                case 'faq':
                                case 'documentation':
                                case 'consul project main website':
                                case 'configuration for production environments':
                                case 'deploy on vercel':
                                case 'related repositories':
                                case 'staging environment setup':
                                case 'add a new client':
                                case 'deploy to stage':
                                case 'deploy to production':
                                case 'current flutter version':
                                    $reportRow['section_techDocumentation'] = "yes";
                                    break;
                                case 'contributing':
                                case 'for project creators':
                                case 'for contributors':
                                case 'contribute':
                                case 'how can i participate?':
                                case 'collaborators':
                                case 'contribute:':
                                case 'contributors:':
                                case 'contributing guide':
                                case 'development team':
                                case 'laravel sponsors':
                                case 'contributions':
                                case 'contribution':
                                    $reportRow['section_contributors'] = "yes";
                                    break;
                                case 'about':
                                case 'descriere':
                                case 'example projects':
                                case 'reguli':
                                case 'duminica':
                                case 'alta intrebare?':
                                case 'community and professional support':
                                case 'something missing?':
                                case 'questions and issues':
                                case 'roadmap':
                                case 'about this repository':
                                case 'credits':
                                case 'what is this project about?':
                                case 'attribution':
                                case 'hackday project boards':
                                case 'assigning tasks':
                                case 'next steps':
                                case 'the problem':
                                case 'the solution':
                                case 'vizualization app to track the covid-19 virus epidemic':
                                case 'overview':
                                case 'code of conduct':
                                case 'current state':
                                case 'new features from forked version':
                                    $reportRow['section_aboutAndDocs'] = "yes";
                                case 'feedback':
                                case 'sending feedback':
                                    $reportRow['section_feedback'] = "yes";
                                    break;
                                case 'license':
                                    $reportRow['section_licence'] = "yes";
                                    break;
                                case 'about code4ro':
                                case 'about code for romania':
                                    $reportRow['section_aboutCode4Ro'] = "yes";
                                    break;
                                case 'donate':
                                    $reportRow['section_donate'] = "yes";
                                    break;
                                case 'politica code for romania anti-hărțuire':
                                case 'template de e-mail pentru raportarea situațiilor de hărțuire':
                                    $reportRow['section_harassment'] = "yes";
                                    break;
                                case 'repos and projects':
                                case 'important, before you start':
                                case 'table of contents':
                                case 'current app version : 1.0.4':
                                    break;
                                default:
                                    throw new RuntimeException(
                                        sprintf(
                                            "H2 Section '%s' not mapped for repo: %s",
                                            strtolower($node->text()),
                                            $repo['html_url']
                                        ));
                            }
                        });

                    $reportRow['raw_identified_sections'] = implode(", ", $rawSections);
                    fputcsv($report, $reportRow);


                } catch (\Github\Exception\RuntimeException $e) {
                    $reportRow['has_readme'] = "no";
                    $io->warning("Failed to get readme for " . $repo['full_name']);
                }
            }


            $reposPage++;
        }  while (count($repos) == 30);

        fclose($report);
    })
    ->run();