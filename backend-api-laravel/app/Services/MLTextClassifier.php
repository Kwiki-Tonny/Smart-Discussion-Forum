<?php

namespace App\Services;

use App\Models\CategoryTerm;
use App\Models\Topic;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MLTextClassifier
{
    protected $categories = [
        // ─── COMPUTER SCIENCE & IT ─────────────────────────────
        'Database Systems' => [
            'sql', 'mysql', 'postgresql', 'mongodb', 'database', 'query', 'join', 'index',
            'normalization', 'transaction', 'acid', 'foreign key', 'primary key',
            'oracle', 'redis', 'cassandra', 'dynamodb', 'rdbms', 'nosql',
            'data warehouse', 'data mining', 'etl', 'olap', 'schema', 'view',
            'stored procedure', 'trigger', 'indexing', 'sharding', 'replication'
        ],

        'Java Programming' => [
            'java', 'class', 'object', 'inheritance', 'polymorphism',
            'jvm', 'jdk', 'spring', 'spring boot', 'hibernate', 'maven',
            'gradle', 'interface', 'abstract', 'encapsulation', 'lambda',
            'stream', 'optional', 'exception', 'thread', 'synchronized',
            'generics', 'collections', 'arraylist', 'hashmap', 'optional',
            'jpa', 'jdbc', 'servlet', 'jsp', 'jsf', 'jakarta', 'jee'
        ],

        'Python Programming' => [
            'python', 'django', 'flask', 'fastapi', 'numpy', 'pandas',
            'matplotlib', 'scikit', 'tensorflow', 'pytorch', 'keras',
            'jupyter', 'conda', 'pip', 'virtualenv', 'list', 'dict',
            'tuple', 'comprehension', 'generator', 'decorator', 'context manager',
            'asyncio', 'multiprocessing', 'threading', 'requests', 'beautifulsoup'
        ],

        'Web Development' => [
            'html', 'html5', 'css', 'css3', 'javascript', 'typescript',
            'react', 'vue', 'angular', 'svelte', 'nextjs', 'nuxtjs',
            'php', 'laravel', 'symfony', 'nodejs', 'express', 'nestjs',
            'api', 'rest', 'graphql', 'ajax', 'websocket', 'sse',
            'webpack', 'vite', 'babel', 'eslint', 'prettier', 'tailwind',
            'bootstrap', 'material', 'ui', 'ux', 'responsive', 'spa', 'pwa'
        ],

        'Network Security' => [
            'firewall', 'encryption', 'ssl', 'tls', 'vpn', 'router',
            'packet', 'cisco', 'ip', 'tcp', 'udp', 'dns', 'dhcp',
            'cybersecurity', 'vulnerability', 'penetration', 'ids', 'ips',
            'malware', 'ransomware', 'phishing', 'social engineering',
            'hashing', 'salt', 'authentication', 'authorization', 'oauth',
            'jwt', 'saml', 'kerberos', 'certificate', 'pki', 'ssh', 'ssl'
        ],

        'Cloud Computing' => [
            'aws', 'amazon', 'ec2', 's3', 'lambda', 'rds', 'dynamodb',
            'azure', 'google cloud', 'gcp', 'compute', 'cloud', 'serverless',
            'docker', 'kubernetes', 'k8s', 'microservices', 'container',
            'orchestration', 'helm', 'terraform', 'ansible', 'cloudformation',
            'load balancer', 'auto scaling', 'cd', 'blue green', 'canary'
        ],

        'DevOps & CI/CD' => [
            'devops', 'ci', 'cd', 'jenkins', 'github actions', 'gitlab ci',
            'circleci', 'build', 'deploy', 'pipeline', 'automation',
            'infrastructure', 'iac', 'terraform', 'ansible', 'puppet',
            'chef', 'monitoring', 'prometheus', 'grafana', 'elk', 'splunk'
        ],

        'Data Science & AI' => [
            'data science', 'machine learning', 'deep learning', 'neural network',
            'artificial intelligence', 'ai', 'ml', 'nlp', 'computer vision',
            'regression', 'classification', 'clustering', 'pca', 'svm',
            'decision tree', 'random forest', 'xgboost', 'knn', 'naive bayes',
            'cnn', 'rnn', 'lstm', 'transformer', 'bert', 'gpt',
            'tensorflow', 'pytorch', 'keras', 'pandas', 'numpy', 'scikit'
        ],

        'Algorithms & Data Structures' => [
            'algorithm', 'sort', 'search', 'recursion', 'complexity',
            'binary', 'tree', 'graph', 'hash', 'heap', 'queue', 'stack',
            'dynamic programming', 'divide', 'conquer', 'backtracking',
            'linked list', 'array', 'time complexity', 'space complexity',
            'dijkstra', 'a*', 'floyd', 'bellman', 'ford', 'prim', 'kruskal'
        ],

        'Operating Systems' => [
            'linux', 'windows', 'macos', 'process', 'thread', 'scheduler',
            'memory', 'file system', 'kernel', 'shell', 'bash', 'zsh',
            'cpu', 'interrupt', 'system call', 'virtual memory', 'paging',
            'segmentation', 'deadlock', 'semaphore', 'mutex', 'monitor',
            'i/o', 'disk', 'scheduling', 'fifo', 'round robin', 'priority'
        ],

        'Software Engineering' => [
            'agile', 'scrum', 'kanban', 'testing', 'unit test', 'debug',
            'refactoring', 'design patterns', 'solid', 'tdd', 'bdd',
            'version control', 'git', 'github', 'gitlab', 'bitbucket',
            'requirements', 'analysis', 'design', 'uml', 'architecture',
            'technical debt', 'code review', 'pair programming', 'retrospective'
        ],

        // ─── SCIENCES ────────────────────────────────────────────
        'Chemistry' => [
            'chemistry', 'chemical', 'reaction', 'molecule', 'compound',
            'acid', 'base', 'salt', 'titration', 'periodic table', 'organic',
            'inorganic', 'biochemistry', 'polymer', 'catalyst', 'enzyme',
            'ph', 'redox', 'oxidation', 'reduction', 'stoichiometry',
            'mole', 'avogadro', 'bonding', 'ionic', 'covalent', 'metallic',
            'thermodynamics', 'kinetics', 'equilibrium', 'spectroscopy'
        ],

        'Physics' => [
            'physics', 'mechanics', 'thermodynamics', 'optics', 'waves',
            'quantum', 'relativity', 'force', 'motion', 'energy',
            'gravity', 'electromagnetism', 'circuit', 'magnetic', 'field',
            'newton', 'einstein', 'schrödinger', 'heisenberg', 'feynman',
            'kinematics', 'dynamics', 'statics', 'fluid', 'gas', 'liquid',
            'solid', 'plasma', 'nuclear', 'particle', 'astrophysics'
        ],

        'Biology' => [
            'biology', 'cell', 'dna', 'rna', 'protein', 'enzyme',
            'genetics', 'evolution', 'ecology', 'photosynthesis',
            'respiration', 'organism', 'tissue', 'organ', 'system',
            'mitosis', 'meiosis', 'mutation', 'natural selection',
            'microbiology', 'bacteriology', 'virology', 'immunology',
            'neuroscience', 'endocrine', 'homeostasis', 'reproduction'
        ],

        'Mathematics' => [
            'mathematics', 'maths', 'algebra', 'calculus', 'geometry',
            'statistics', 'probability', 'linear algebra', 'differential',
            'integral', 'matrix', 'vector', 'equation', 'function', 'graph',
            'theorem', 'proof', 'logic', 'number theory', 'combinatorics',
            'topology', 'analysis', 'trigonometry', 'set theory', 'category'
        ],

        'Environmental Science' => [
            'environmental', 'ecology', 'climate', 'change', 'global warming',
            'sustainability', 'renewable', 'energy', 'conservation',
            'biodiversity', 'ecosystem', 'pollution', 'carbon', 'footprint',
            'waste', 'recycling', 'green', 'clean', 'water', 'air', 'soil'
        ],

        // ─── ENGINEERING ──────────────────────────────────────────
        'Mechanical Engineering' => [
            'mechanical', 'machinery', 'design', 'manufacturing', 'automotive',
            'aerospace', 'robotics', 'mechatronics', 'thermodynamics', 'fluid',
            'solid mechanics', 'stress', 'strain', 'material', 'metal',
            'plastic', 'composite', 'casting', 'forging', 'welding', 'cnc'
        ],

        'Electrical Engineering' => [
            'electrical', 'circuit', 'power', 'electronics', 'signal',
            'control', 'system', 'embedded', 'microcontroller', 'arduino',
            'raspberry pi', 'fpga', 'verilog', 'vhdl', 'pcb', 'soldering',
            'oscilloscope', 'multimeter', 'sensor', 'actuator', 'motor'
        ],

        'Civil Engineering' => [
            'civil', 'construction', 'structural', 'infrastructure',
            'bridge', 'building', 'road', 'highway', 'railway', 'tunnel',
            'dam', 'pipe', 'pipeline', 'foundation', 'soil', 'concrete',
            'steel', 'timber', 'surveying', 'drainage', 'water supply'
        ],

        // ─── BUSINESS & ECONOMICS ────────────────────────────────
        'Business Management' => [
            'business', 'management', 'leadership', 'strategy', 'marketing',
            'finance', 'accounting', 'hr', 'human resources', 'entrepreneurship',
            'startup', 'innovation', 'operations', 'supply chain', 'logistics',
            'organizational', 'culture', 'team', 'project', 'product', 'sales'
        ],

        'Economics' => [
            'economics', 'economy', 'microeconomics', 'macroeconomics',
            'market', 'demand', 'supply', 'inflation', 'unemployment',
            'gdp', 'growth', 'development', 'trade', 'finance', 'banking',
            'monetary', 'fiscal', 'tax', 'budget', 'capital', 'labour'
        ],

        // ─── HUMANITIES & SOCIAL SCIENCES ──────────────────────
        'Literature & Languages' => [
            'literature', 'poetry', 'novel', 'drama', 'shakespeare',
            'linguistics', 'language', 'translation', 'grammar', 'syntax',
            'semantics', 'phonetics', 'morphology', 'dialect', 'accent',
            'writing', 'reading', 'criticism', 'analysis', 'interpretation'
        ],

        'History & Politics' => [
            'history', 'historical', 'ancient', 'medieval', 'modern',
            'politics', 'political', 'government', 'democracy', 'election',
            'war', 'peace', 'revolution', 'colonial', 'empire', 'nationalism',
            'globalization', 'diplomacy', 'treaty', 'alliance', 'conflict'
        ],

        'Philosophy & Ethics' => [
            'philosophy', 'ethics', 'moral', 'logic', 'reasoning',
            'epistemology', 'ontology', 'metaphysics', 'socrates', 'plato',
            'aristotle', 'descartes', 'kant', 'nietzsche', 'existentialism',
            'utilitarianism', 'deontology', 'virtue', 'justice', 'rights'
        ],

        'Psychology' => [
            'psychology', 'cognitive', 'behavioural', 'clinical', 'therapy',
            'mental health', 'anxiety', 'depression', 'stress', 'wellbeing',
            'personality', 'social', 'developmental', 'child', 'adolescent',
            'aging', 'memory', 'perception', 'learning', 'emotion', 'motivation'
        ],

        'Education & Teaching' => [
            'education', 'teaching', 'learning', 'pedagogy', 'curriculum',
            'assessment', 'evaluation', 'lesson', 'plan', 'classroom',
            'student', 'teacher', 'school', 'college', 'university',
            'distance learning', 'online', 'blended', 'training', 'development'
        ],

        'Law & Legal Studies' => [
            'law', 'legal', 'constitutional', 'criminal', 'civil',
            'contract', 'tort', 'property', 'family', 'employment',
            'human rights', 'international', 'court', 'judge', 'jury',
            'legislation', 'statute', 'common law', 'equity', 'justice'
        ],

        // ─── MEDICAL & HEALTH ────────────────────────────────────
        'Medicine & Health' => [
            'medicine', 'medical', 'health', 'healthcare', 'clinical',
            'disease', 'diagnosis', 'treatment', 'surgery', 'therapy',
            'pharmacy', 'pharmacology', 'anatomy', 'physiology', 'pathology',
            'microbiology', 'immunology', 'cardiology', 'neurology', 'oncology'
        ],

        // ─── OTHER ───────────────────────────────────────────────
        'General Discussion' => [],
        'General Research' => [
            'research', 'study', 'analysis', 'investigation', 'experiment',
            'hypothesis', 'theory', 'framework', 'methodology', 'literature review',
            'data collection', 'sample', 'findings', 'results', 'discussion',
            'conclusion', 'recommendation', 'publication', 'journal', 'paper'
        ],
    ];

    public function classify($title, $body, $groupId): string
    {
        $text = strtolower($title . ' ' . $body);
        $text = preg_replace('/[^a-z0-9 ]/', ' ', $text);
        $words = array_count_values(array_filter(explode(' ', $text)));

        $scores = [];

        foreach ($this->categories as $category => $keywords) {
            $score = 0;
            if (empty($keywords)) continue;

            foreach ($keywords as $keyword) {
                if (isset($words[$keyword])) {
                    $importance = $this->getGlobalImportance($keyword, $groupId);
                    $score += ($words[$keyword] * $importance);
                }
            }
            $scores[$category] = $score;
        }

        if (empty($scores) || max($scores) === 0) {
            return 'General Discussion';
        }

        arsort($scores);
        $topCategory = key($scores);
        $this->updateTermFrequencies($words, $groupId, $topCategory);

        return $topCategory;
    }

    protected function getGlobalImportance($term, $groupId): float
    {
        $cacheKey = "term_importance_{$groupId}_{$term}";

        return Cache::remember($cacheKey, 3600, function () use ($term, $groupId) {
            $totalTopics = Topic::where('group_id', $groupId)->count();

            if ($totalTopics === 0) return 1.0;

            $termCount = CategoryTerm::where('group_id', $groupId)
                ->where('term', $term)
                ->sum('frequency');

            $importance = 1 + log($totalTopics / max(1, $termCount));
            return round($importance, 2);
        });
    }

    protected function updateTermFrequencies($words, $groupId, $category): void
    {
        foreach ($words as $term => $frequency) {
            if (strlen($term) < 3) continue;

            CategoryTerm::updateOrCreate(
                ['term' => $term, 'group_id' => $groupId],
                [
                    'category'  => $category,
                    'frequency' => DB::raw('frequency + ' . $frequency),
                ]
            );
        }
    }

    public static function recalculateImportance($groupId): void
    {
        $totalTopics = Topic::where('group_id', $groupId)->count();
        if ($totalTopics === 0) return;

        $terms = CategoryTerm::where('group_id', $groupId)->get();
        foreach ($terms as $term) {
            $cacheKey = "term_importance_{$groupId}_{$term->term}";
            $importance = 1 + log($totalTopics / max(1, $term->frequency));
            Cache::put($cacheKey, round($importance, 2), 3600);
        }
    }
}