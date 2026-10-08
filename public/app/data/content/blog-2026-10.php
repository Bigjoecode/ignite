<?php
// Three research articles supplied by the practice (Google Doc, October 2026).
// Loaded with: php app/cli/import-posts.php app/data/content/blog-2026-10.php
//
// Changes made to the supplied copy, all deliberate:
//  - the notes to the publisher at the top of each tab were removed
//  - the tables, which the document export flattened into lines, were rebuilt
//  - the CareCredit source is named in plain text, because the site never links out
//  - "household income" in the second headline became "earnings", because the study's
//    own method section says the measure is median salary, not household income
//  - the benchmark sentence in the third article was unreadable ("dividing the
//    unweighted average braces cost across the 50 states (80,902.92)"); it now states
//    the ratio being described without inventing a figure

$costTable = static function (array $rows, string $caption, bool $ranked = true): string {
    $head = $ranked ? "<th scope=\"col\">Rank</th>" : '';
    $html = "<table>\n<caption>{$caption}</caption>\n<thead>\n<tr>{$head}"
        . "<th scope=\"col\">State</th><th scope=\"col\">Average metal braces cost</th>"
        . "<th scope=\"col\">Median salary</th><th scope=\"col\">Cost as % of median salary</th></tr>\n</thead>\n<tbody>\n";
    foreach ($rows as $i => [$state, $cost, $salary, $share]) {
        $rank = $ranked ? '<td>' . ($i + 1) . '</td>' : '';
        $html .= "<tr>{$rank}<th scope=\"row\">{$state}</th><td>{$cost}</td><td>{$salary}</td><td>{$share}</td></tr>\n";
    }
    return $html . "</tbody>\n</table>";
};

// the same ten states lead every cut of the data
$highest = [
    ['Louisiana', '$7,509', '$60,986', '12.31%'],
    ['Mississippi', '$6,362', '$59,127', '10.76%'],
    ['Oklahoma', '$6,940', '$66,148', '10.49%'],
    ['Nevada', '$8,350', '$81,134', '10.29%'],
    ['Kansas', '$7,735', '$75,514', '10.24%'],
    ['Missouri', '$7,280', '$71,589', '10.17%'],
    ['West Virginia', '$6,069', '$60,798', '9.98%'],
    ['Michigan', '$7,193', '$72,389', '9.94%'],
    ['Ohio', '$7,127', '$72,212', '9.87%'],
    ['Arkansas', '$6,043', '$62,106', '9.73%'],
];
$lowest = [
    ['Utah', '$5,853', '$96,658', '6.06%'],
    ['California', '$6,087', '$100,149', '6.08%'],
    ['Maryland', '$6,278', '$102,905', '6.10%'],
    ['Connecticut', '$5,927', '$96,049', '6.17%'],
    ['New Hampshire', '$6,157', '$99,782', '6.17%'],
    ['Alaska', '$6,082', '$95,665', '6.36%'],
    ['Washington', '$6,370', '$99,389', '6.41%'],
    ['Indiana', '$4,767', '$71,959', '6.62%'],
    ['Virginia', '$6,151', '$92,090', '6.68%'],
    ['Massachusetts', '$7,007', '$104,828', '6.68%'],
];
// the third article's table includes New Mexico and stops at Ohio
$benchmarkTop = [
    ['Louisiana', '$7,509', '$60,986', '12.31%'],
    ['Mississippi', '$6,362', '$59,127', '10.76%'],
    ['Oklahoma', '$6,940', '$66,148', '10.49%'],
    ['Nevada', '$8,350', '$81,134', '10.29%'],
    ['Kansas', '$7,735', '$75,514', '10.24%'],
    ['Missouri', '$7,280', '$71,589', '10.17%'],
    ['New Mexico', '$6,783', '$67,816', '10.00%'],
    ['West Virginia', '$6,069', '$60,798', '9.98%'],
    ['Michigan', '$7,193', '$72,389', '9.94%'],
    ['Ohio', '$7,127', '$72,212', '9.87%'],
];

$sources = <<<'HTML'
<h2>Sources</h2>
<ul>
<li>CareCredit, &ldquo;How Much Do Dental Braces Cost? Pricing and Insurance Guide&rdquo; (carecredit.com), for the state-level braces cost estimates.</li>
<li>A state-level median salary dataset, supplied for this analysis.</li>
</ul>
HTML;

return [
    'posts' => [

/* ================================================================ */
[
    'slug'        => 'us-braces-affordability-index',
    'title'       => 'The US Braces Affordability Index: Where Braces Take the Biggest Bite Out of Earnings',
    'description' => 'A study by Ignite Orthodontics comparing average metal braces costs with median salary in all 50 states. Louisiana has the highest ratio at 12.3%, Utah the lowest at 6.1%.',
    'category'    => 'Costs & Research',
    'published'   => '2026-10-08',
    'image'       => '/assets/img/IMG_20260814_125146.jpg',
    'image_alt'   => 'Close-up of a smile with metal braces',
    'body'        => <<<HTML
<p>The cost of getting braces can vary considerably across the United States. But treatment prices alone do not tell the whole story: the same dental expense can represent a very different share of earnings depending on where someone lives.</p>
<p>The US Braces Affordability Index, developed by Ignite Orthodontics, compares average metal braces costs with median salary across all 50 US states to identify where treatment costs represent a larger or smaller proportion of typical earnings.</p>

<h2>Key findings</h2>
<ul>
<li><strong>Louisiana ranks first in relative cost.</strong> Average metal braces cost $7,509, equivalent to approximately 12.3% of the state's median salary of $60,986.</li>
<li><strong>Mississippi ranks second</strong>, where braces cost $6,362, or approximately 10.8% of median salary.</li>
<li><strong>Oklahoma ranks third.</strong> The estimated $6,940 cost of braces represents approximately 10.5% of median salary.</li>
<li><strong>Nevada ranks fourth.</strong> Its $8,350 estimated braces cost represents approximately 10.3% of median salary.</li>
<li><strong>Kansas rounds out the top five</strong>, where braces cost $7,735, equivalent to approximately 10.2% of median salary.</li>
<li><strong>Utah has the lowest cost-to-salary ratio.</strong> Its estimated $5,853 braces cost represents approximately 6.1% of median salary.</li>
<li>California and Maryland also rank among the states with the lowest ratios, at approximately 6.1% each.</li>
</ul>
<p>These rankings show that the states with the highest braces prices are not necessarily the states where braces account for the largest share of earnings. The relationship between treatment costs and salary changes the picture.</p>

<h2>The 10 states where braces cost the largest share of median salary</h2>
<p>The following states have the highest ratio of average metal braces cost to median salary in the dataset.</p>
{$costTable($highest, 'States where braces account for the largest share of median salary')}
<p>Calculation: average metal braces cost &divide; median salary &times; 100. Percentages are rounded to two decimal places.</p>
<p>Louisiana leads the ranking, with the estimated cost of metal braces equivalent to approximately 12.3% of median salary. Mississippi and Oklahoma follow, at approximately 10.8% and 10.5% respectively.</p>
<p>Nevada has the highest average braces cost in the top 10, at $8,350. However, its relative cost is lower than Louisiana's because the median salary figure used in the analysis is also higher.</p>
<p>Michigan and Ohio also feature in the top 10, with estimated braces costs equivalent to approximately 9.9% of median salary in each state.</p>

<h2>The 10 states where braces cost the smallest share of median salary</h2>
<p>At the other end of the index, these states have the lowest ratios between average metal braces cost and median salary.</p>
{$costTable($lowest, 'States where braces account for the smallest share of median salary')}
<p>States are ordered by the calculated cost-to-salary ratio before rounding.</p>
<p>Utah has the lowest ratio in the analysis, with the estimated cost of metal braces equivalent to approximately 6.1% of median salary. California and Maryland follow closely: both have estimated braces costs above $6,000, but their median salary figures are also relatively high.</p>
<p>Indiana illustrates a different pattern. It has the lowest average braces cost in the dataset, at $4,767, helping it achieve one of the lowest cost-to-salary ratios despite a lower median salary than several other states on this list.</p>

<h2>What the rankings tell us about braces costs</h2>
<p>The comparison highlights why treatment prices should be considered alongside earnings when examining the cost of orthodontic care.</p>
<p>Louisiana has an estimated braces cost of $7,509, while Utah's estimate is $5,853. However, the cost-to-salary ratio in Louisiana is approximately twice Utah's ratio.</p>
<p>This does not mean every resident in Louisiana will find braces less affordable than every resident in Utah. Individual circumstances, household income, insurance coverage, financing and treatment needs all affect what a patient can afford. Instead, the index provides a state-level comparison of the relationship between estimated treatment costs and median salary.</p>

<h2>How we calculated the index</h2>
<p>The study uses two measures for each state: the state-level average metal braces cost, attributed to CareCredit, and the state-level median salary figure from the salary dataset used for this analysis.</p>
<p>We calculated the cost-to-salary ratio by dividing average metal braces cost by median salary and multiplying the result by 100. For example, Louisiana's calculation is $7,509 &divide; $60,986 &times; 100 = approximately 12.31%.</p>
<p>A higher percentage means the estimated braces cost represents a larger share of the median salary figure. A lower percentage means it represents a smaller share.</p>
<p>The index does not directly measure disposable income, household affordability or the proportion of residents who need or cannot afford orthodontic treatment. It also does not account for insurance benefits, taxes, living expenses, financing or differences in individual treatment plans.</p>

<h2>What to consider before getting braces</h2>
<p>For people considering orthodontic treatment, state-level averages can provide context, but they are not a substitute for an individual quote.</p>
<p>The final cost of treatment can depend on the type and complexity of the case, treatment duration, provider fees, insurance benefits and the services included in a treatment plan.</p>
<p>Ask an orthodontic provider for a written estimate, check whether your dental insurance includes orthodontic benefits, and discuss payment schedules or financing options before starting treatment.</p>

<h2>About the study</h2>
<p>The US Braces Affordability Index was developed by Ignite Orthodontics to compare estimated metal braces costs with median salary across the 50 US states. The findings represent a comparison of state-level estimates, not the actual cost experienced by every patient. The index should not be interpreted as a measure of the number of residents who need braces or who cannot afford treatment.</p>
{$sources}
HTML,
    'faq' => [
        ['Which state has the highest braces cost relative to earnings?', 'Louisiana. The average metal braces cost of $7,509 is equivalent to approximately 12.31% of the state median salary of $60,986, the highest ratio in the 50-state dataset.'],
        ['Which state has the lowest?', 'Utah, where an average braces cost of $5,853 represents approximately 6.06% of a median salary of $96,658. California and Maryland are close behind at approximately 6.1% each.'],
        ['Does a high braces price always mean braces are less affordable there?', 'No. Nevada has the highest average braces cost in the dataset at $8,350, but ranks fourth rather than first because its median salary is also higher. The ratio depends on both the price and local earnings.'],
        ['How much do braces cost in Michigan?', 'The dataset puts the average cost of metal braces in Michigan at $7,193, equivalent to approximately 9.94% of the state median salary of $72,389. Your own cost depends on your treatment plan, your insurance and the payment arrangement you choose.'],
    ],
],

/* ================================================================ */
[
    'slug'        => 'braces-cost-share-of-earnings-by-state',
    'title'       => 'Where Braces Take the Biggest Bite Out of Earnings Across the US',
    'description' => 'Estimated metal braces costs range from $4,767 in Indiana to $8,350 in Nevada. Comparing those prices with median salary shows where treatment represents the largest share of earnings.',
    'category'    => 'Costs & Research',
    'published'   => '2026-10-08',
    'image'       => '/assets/img/happy-family.jpg',
    'image_alt'   => 'Parents and a child smiling together',
    'body'        => <<<HTML
<p>For families considering braces, the price of treatment is only part of the financial picture. The cost must also be considered alongside the income available to pay for it.</p>
<p>The estimated cost of metal braces varies across the United States, from $4,767 in Indiana to $8,350 in Nevada, according to the state-level figures used in this analysis. But a higher treatment price does not automatically mean a greater relative financial burden: earnings differ from state to state, too.</p>
<p>To explore these differences, Ignite Orthodontics compared average metal braces costs with median salary across the 50 US states, to identify where the estimated cost of treatment represents a larger share of typical earnings, and where the gap between treatment costs and earnings is smaller.</p>

<h2>Key findings</h2>
<ul>
<li><strong>Louisiana:</strong> the estimated $7,509 cost of metal braces is equivalent to approximately 12.3% of the state's median salary of $60,986.</li>
<li><strong>Mississippi:</strong> an estimated cost of $6,362 represents approximately 10.8% of median salary.</li>
<li><strong>Oklahoma:</strong> the estimated $6,940 cost represents approximately 10.5% of median salary.</li>
<li><strong>Nevada:</strong> despite having the highest estimated braces cost in the dataset, at $8,350, Nevada's cost-to-salary ratio is approximately 10.3%.</li>
<li><strong>Utah:</strong> the estimated $5,853 cost represents approximately 6.1% of median salary, among the lowest ratios in the analysis.</li>
<li><strong>California:</strong> an estimated cost of $6,087 represents approximately 6.1% of median salary.</li>
</ul>
<p>These comparisons illustrate why families may want to consider treatment prices relative to earnings rather than looking at the price alone.</p>

<h2>The states where braces take up the largest share of earnings</h2>
<p>The following table ranks the 10 states where average metal braces costs account for the largest proportion of median salary.</p>
{$costTable($highest, 'The 10 highest braces-cost-to-salary ratios')}
<p>The ratio is calculated by dividing average metal braces cost by median salary and multiplying by 100. Figures are rounded to two decimal places.</p>
<p>Louisiana has the highest ratio in the dataset. The estimated cost of braces is equivalent to about 12.3% of median salary, compared with around 6.1% in Utah. That difference highlights how the same type of treatment can represent a different proportion of earnings depending on the state.</p>

<h2>Where the gap between braces costs and earnings is smaller</h2>
<p>The next table shows the 10 states with the lowest braces-cost-to-median-salary ratios.</p>
{$costTable($lowest, 'The 10 lowest braces-cost-to-salary ratios')}
<p>States are ordered by the calculated ratio before rounding.</p>
<p>The results show that a state's position depends on both sides of the comparison: the estimated price of braces and the median salary figure.</p>
<p>For example, Massachusetts has one of the highest braces cost estimates among the states in the lower-ratio group, at $7,007. Its median salary figure of $104,828 means that the estimated treatment cost represents approximately 6.7% of median salary. Indiana, by contrast, has the lowest estimated braces cost in the dataset, at $4,767, and a cost-to-salary ratio of approximately 6.6%.</p>

<h2>The difference between the highest and lowest relative costs</h2>
<p>In Louisiana, the estimated cost of braces represents approximately 12.31% of median salary. In Utah, it represents approximately 6.06%. That is a difference of approximately 6.25 percentage points, and Louisiana's ratio is just over twice Utah's.</p>
<p>This comparison does not mean that every household in Louisiana faces a greater financial challenge than every household in Utah. Median salary is a state-level measure, and individual families have different earnings, expenses, savings, insurance benefits and treatment needs. However, it does demonstrate why looking at treatment costs alongside earnings can provide a different perspective from comparing prices alone.</p>

<h2>What this means for families planning treatment</h2>
<p>For families, braces are a significant expense to plan for. The final amount a patient pays can vary depending on the complexity of treatment, the provider, the length of treatment, insurance benefits and the services included in the treatment plan.</p>
<p>State-level averages can help families understand the broader cost landscape, but they cannot predict an individual's bill or determine whether a particular family can afford treatment.</p>
<p>Before starting treatment, ask providers for a written estimate, check your dental insurance benefits and discuss payment schedules or financing options. Comparing the total treatment price and the terms of any payment plan can also help families understand the commitment involved.</p>

<h2>How the study was calculated</h2>
<p>Ignite Orthodontics compared state-level estimates for metal braces with median salary figures for all 50 states. The calculation is: braces cost as a percentage of median salary = (average metal braces cost &divide; median salary) &times; 100.</p>
<p>A higher percentage indicates that the estimated cost of treatment represents a larger share of the median salary figure. A lower percentage indicates a smaller share.</p>
<p>The analysis does not directly measure household income, disposable income, financial hardship or the number of families unable to afford braces. It also does not account for insurance coverage, taxes, household expenses or financing arrangements. Because the salary measure is median salary rather than household income, the findings are described throughout as a comparison with median salary.</p>
{$sources}
HTML,
    'faq' => [
        ['What is the cheapest state for metal braces?', 'Indiana has the lowest average cost in the dataset at $4,767. The most expensive is Nevada at $8,350.'],
        ['Why does Nevada have the highest price but not the highest relative cost?', 'Because the ratio compares cost with earnings. Nevada\'s median salary of $81,134 is higher than Louisiana\'s $60,986, so the same kind of treatment accounts for a smaller share of typical earnings there.'],
        ['Does this tell me what I will pay?', 'No. These are state-level averages. What you pay depends on your treatment plan, how long treatment takes, your provider, your insurance benefits and the payment arrangement you choose. Ask for a written estimate before starting.'],
    ],
],

/* ================================================================ */
[
    'slug'        => 'americans-living-where-braces-cost-more',
    'title'       => 'How Many Americans Live in States Where Braces Cost More Relative to Earnings?',
    'description' => 'Around 164 million people, roughly 47.7% of the population across the 50 states, live in states where metal braces cost more relative to median salary than the national benchmark of 7.87%.',
    'category'    => 'Costs & Research',
    'published'   => '2026-10-08',
    'image'       => '/assets/img/IMG_20260814_125736.jpg',
    'image_alt'   => 'A family smiling together outdoors',
    'body'        => <<<HTML
<p>New analysis finds that around 164 million people live in US states where the cost of metal braces is above the national state-level benchmark relative to median salary.</p>
<p>The analysis found that 25 states have above-benchmark braces costs relative to median salary. Together, these states have a combined population of approximately 163.6 million people, around 47.7% of the combined population across the 50 states in the dataset.</p>
<p>The findings highlight how the financial weight of orthodontic treatment can vary across the country, even before considering insurance coverage, payment plans or differences in individual household circumstances.</p>

<h2>Nearly half the population lives in above-benchmark states</h2>
<p>The analysis compared the average cost of metal braces in each state with its median salary, then identified states where the resulting cost-to-salary ratio exceeded the benchmark calculated from the 50-state dataset. That benchmark was approximately 7.87% of median annual salary.</p>
<p>States above this benchmark accounted for about 163.6 million residents, based on 2026 state population estimates.</p>
<p>This does not mean that 163.6 million people need braces or personally face higher treatment costs. It measures the population living in states where the state-level cost-to-salary ratio is above the benchmark.</p>

<h2>Which states have the highest relative braces costs?</h2>
<p>Louisiana recorded the highest braces-cost-to-median-salary ratio in the dataset. The average cost of metal braces was $7,509, equivalent to approximately 12.31% of the state's median salary of $60,986. Mississippi ranked next at 10.76%, followed by Oklahoma at 10.49%.</p>
<p>Nevada and Kansas also recorded ratios above 10%, reflecting the relationship between local braces costs and median earnings rather than treatment prices alone.</p>
{$costTable($benchmarkTop, 'The 10 highest braces-cost-to-salary ratios', false)}
<p>Percentages are calculated by dividing the average braces cost by the median salary for each state and rounding to two decimal places.</p>

<h2>Population size changes the picture</h2>
<p>Looking at the relative cost of braces alone does not show how many people live in each state. Adding population estimates provides another way to understand the scale.</p>
<p>Texas, for example, has a lower relative braces cost than several states near the top of the ranking. However, its population of approximately 32.1 million means it contributes a substantial number of residents to the overall population living in above-benchmark states.</p>
<p>Florida is similar, with approximately 23.7 million residents and a braces-cost-to-median-salary ratio of 8.00%, above the 7.87% benchmark. By contrast, Louisiana has a much higher relative cost ratio of 12.31%, but a smaller population of approximately 4.6 million.</p>
<p>These differences demonstrate why population-weighted context can complement a state-by-state affordability comparison.</p>

<h2>The states below the benchmark</h2>
<p>The remaining 25 states recorded braces-cost-to-median-salary ratios at or below the 7.87% benchmark.</p>
<p>A lower ratio does not necessarily mean braces are inexpensive in absolute terms. It means the average cost of treatment represents a smaller share of the median salary figure. California's average braces cost was $6,087, or approximately 6.08% of its median salary of $100,149. Utah's ratio was approximately 6.06%, based on an average braces cost of $5,853 and a median salary of $96,658.</p>
<p>These results underline the distinction between the price of treatment and its cost relative to earnings.</p>

<h2>What the findings mean for families</h2>
<p>Orthodontic treatment can involve a substantial upfront or ongoing expense. The analysis offers a state-level comparison that may help families understand how typical treatment costs relate to the salary figures used in the study.</p>
<p>However, the ratio is not a direct measure of affordability for every family. Actual costs and financial circumstances vary according to the treatment plan, provider, insurance benefits, financing arrangements, household income and other expenses.</p>
<p>The figures should therefore be interpreted as a comparison of state-level averages and median salaries, not as evidence that residents in any particular state cannot afford orthodontic treatment.</p>

<h2>Methodology</h2>
<p>This analysis combines three datasets: average metal braces costs by state, attributed to CareCredit; a state-level median salary dataset; and 2026 state population estimates attributed to World Population Review.</p>
<p>For each state, the braces-cost-to-salary ratio was calculated as average cost of metal braces &divide; median salary &times; 100.</p>
<p>The benchmark of approximately 7.87% is the ratio between the unweighted average braces cost across the 50 states and the unweighted average median salary of $80,902.92. States with ratios greater than 7.87% were classified as above benchmark, and their population estimates added together, producing a total of approximately 163.6 million people across 25 states. The population share was calculated against the combined population of the 50 states in the dataset, approximately 342.9 million.</p>
<p><strong>Limitations.</strong> The population total represents residents of states above the benchmark, not the number of people who need braces or experience financial difficulty paying for treatment. The salary figures are median salary rather than verified household income.</p>
{$sources}
HTML,
    'faq' => [
        ['What is the 7.87% benchmark?', 'It is the ratio between the unweighted average braces cost across the 50 states and the unweighted average median salary of $80,902.92. States above it were classified as above benchmark.'],
        ['How many people live in above-benchmark states?', 'Approximately 163.6 million across 25 states, around 47.7% of the combined population of the 50 states in the dataset.'],
        ['Does this mean 164 million people cannot afford braces?', 'No. It counts residents of states where the state-level cost-to-salary ratio is above the benchmark. It says nothing about how many people need braces or can afford treatment.'],
    ],
],

    ],
];
