<?php
/**
 * GSLEY Diagnostics — tool configuration bank.
 *
 * One entry per tool. Six use the shared Likert engine (only CONFIG differs);
 * two are variant engines (calculator, finder). Returned to the client as JSON
 * by the block render callback, and used server-side to validate tool ids.
 *
 * @package GSLEY_Diagnostics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array<string,array> Keyed by tool slug (matches /tools/{slug}).
 */
function gsley_diagnostics_configs() {
	$scale = array(
		array( 'label' => 'Strongly disagree', 'value' => 0 ),
		array( 'label' => 'Disagree', 'value' => 1 ),
		array( 'label' => 'Neither agree nor disagree', 'value' => 2 ),
		array( 'label' => 'Agree', 'value' => 3 ),
		array( 'label' => 'Strongly agree', 'value' => 4 ),
	);

	$ladder_bands = array(
		array( 'min' => 0,  'max' => 40,  'name' => 'Ad hoc',       'blurb' => 'The engine runs on individual effort and luck. Wins happen, but they don’t repeat or compound. The priority is to install a system where one is currently absent.' ),
		array( 'min' => 41, 'max' => 65,  'name' => 'Emerging',     'blurb' => 'Some systems exist, but inconsistently. Performance is improving yet still fragile — dependent on a few people and prone to slipping. Make the strongest area repeatable first.' ),
		array( 'min' => 66, 'max' => 85,  'name' => 'Systematised', 'blurb' => 'The core is documented and instrumented. The engine works without heroics. The priority now is to compound — turning a working system into a widening advantage.' ),
		array( 'min' => 86, 'max' => 100, 'name' => 'Compounding',  'blurb' => 'The system is engineered and compounding. Marginal effort produces increasing return. The priority is to defend the lead and extend it into adjacent areas.' ),
	);

	$report_lines = array(
		'Full dimension breakdown with interpretation',
		'A sequenced 90-day action plan',
		'The archetype matched to each gap',
	);

	$configs = array();

	/* ---------------- T-01 · Growth Diagnostic (BUILT reference) ---------------- */
	$configs['growth-diagnostic'] = array(
		'id' => 'growth-diagnostic', 'engine' => 'likert', 'code' => 'Diagnostic · T-01 · Flagship',
		'title' => 'The Gsley Growth Diagnostic™',
		'lede' => 'Twelve statements across the four parts of a commercial engine — demand, conversion, retention, and capacity. Answer honestly and you’ll get an instant scored read on where the system is leaking, and which sprint to start with.',
		'startLabel' => 'Begin the diagnostic', 'scoreLabel' => 'Your commercial-system score',
		'facts' => array( array( 'v' => '4 min', 'k' => 'To complete' ), array( 'v' => '12', 'k' => 'Statements' ), array( 'v' => 'Free', 'k' => 'Instant read-out' ) ),
		'scale' => $scale,
		'dimensions' => array(
			array( 'id' => 'demand', 'label' => 'Demand' ),
			array( 'id' => 'conversion', 'label' => 'Conversion' ),
			array( 'id' => 'retention', 'label' => 'Retention & Expansion' ),
			array( 'id' => 'capacity', 'label' => 'Capacity & Systems' ),
		),
		'questions' => array(
			array( 'dim' => 'demand', 'text' => 'We generate qualified pipeline predictably each month, not in unpredictable spikes.' ),
			array( 'dim' => 'demand', 'text' => 'Our demand is aimed at a defined set of target accounts, not broad, untargeted reach.' ),
			array( 'dim' => 'demand', 'text' => 'Our marketing compounds over time rather than resetting with each new campaign.' ),
			array( 'dim' => 'conversion', 'text' => 'We have a documented, repeatable process from qualified lead to closed deal.' ),
			array( 'dim' => 'conversion', 'text' => 'Our pricing and packaging are deliberate, and we rarely discount under pressure.' ),
			array( 'dim' => 'conversion', 'text' => 'Our revenue forecast is reliable and grounded in pipeline evidence.' ),
			array( 'dim' => 'retention', 'text' => 'We measure and actively manage churn and retention.' ),
			array( 'dim' => 'retention', 'text' => 'We have a deliberate motion for growing revenue from existing customers.' ),
			array( 'dim' => 'retention', 'text' => 'We understand our unit economics well enough to act on them.' ),
			array( 'dim' => 'capacity', 'text' => 'Our data is unified and trusted across the business.' ),
			array( 'dim' => 'capacity', 'text' => 'We’ve automated repetitive workflows rather than relying on manual effort.' ),
			array( 'dim' => 'capacity', 'text' => 'We’re adopting AI in a governed, structured way — not ad hoc experiments.' ),
		),
		'bands' => $ladder_bands,
		'recommendations' => array(
			'demand'     => array( 'title' => 'Start with demand.', 'body' => 'Your weakest area is the top of the system — predictable, targeted pipeline. Begin where demand is engineered as a compounding asset rather than a series of campaigns.', 'linkText' => 'Explore Demand Generation', 'url' => '/services/market/demand-generation/' ),
			'conversion' => array( 'title' => 'Start with the commercial spine.', 'body' => 'Demand exists, but it’s leaking before it becomes revenue. The priority is the machinery between a lead and a signed deal — CRM discipline, playbooks, and pricing logic.', 'linkText' => 'Explore B2B Sales Enablement', 'url' => '/services/market/b2b-sales-enablement/' ),
			'retention'  => array( 'title' => 'Start with revenue you already have.', 'body' => 'The cheapest growth is the revenue you’re currently leaking. The priority is pricing, retention, and expansion economics — modelled and improved against evidence.', 'linkText' => 'Explore Revenue Optimisation', 'url' => '/services/digital/revenue-optimisation/' ),
			'capacity'   => array( 'title' => 'Start with capacity.', 'body' => 'The commercial side is ahead of the operational one. The priority is releasing capacity — governed AI, automation, and unified data — so the engine can scale without breaking.', 'linkText' => 'Explore AI Agent Deployment', 'url' => '/services/digital/ai-agent-deployment/' ),
		),
		'reportLines' => $report_lines,
	);

	/* ---------------- T-02 · AI Adoption Assessment — Organisation ---------------- */
	$configs['ai-adoption-assessment-organisation'] = array(
		'id' => 'ai-adoption-assessment-organisation', 'engine' => 'likert', 'code' => 'Diagnostic · T-02',
		'title' => 'AI Adoption Assessment — Organisation',
		'lede' => 'Fifteen statements across the five foundations of organisational AI maturity. You’ll get a board-ready score and the one foundation to strengthen first — before any email is requested.',
		'startLabel' => 'Begin the assessment', 'scoreLabel' => 'Your AI maturity score',
		'facts' => array( array( 'v' => '6 min', 'k' => 'To complete' ), array( 'v' => '15', 'k' => 'Statements' ), array( 'v' => 'Board-ready', 'k' => 'Read-out' ) ),
		'scale' => $scale,
		'dimensions' => array(
			array( 'id' => 'strategy', 'label' => 'Strategy' ),
			array( 'id' => 'data', 'label' => 'Data foundation' ),
			array( 'id' => 'governance', 'label' => 'Governance' ),
			array( 'id' => 'capability', 'label' => 'Capability & skills' ),
			array( 'id' => 'culture', 'label' => 'Culture & adoption' ),
		),
		'questions' => array(
			array( 'dim' => 'strategy', 'text' => 'Leadership has a documented AI strategy tied to specific commercial outcomes.' ),
			array( 'dim' => 'strategy', 'text' => 'There is a named owner accountable for AI adoption across the organisation.' ),
			array( 'dim' => 'strategy', 'text' => 'AI investment is prioritised against business value, not novelty.' ),
			array( 'dim' => 'data', 'text' => 'Our data is unified and clean enough to support reliable AI outputs.' ),
			array( 'dim' => 'data', 'text' => 'Teams can access the data they need without manual workarounds.' ),
			array( 'dim' => 'data', 'text' => 'We know where our data lives and who is permitted to use it.' ),
			array( 'dim' => 'governance', 'text' => 'AI use is governed by an explicit policy covering bias, data residency, and human oversight.' ),
			array( 'dim' => 'governance', 'text' => 'Every AI-assisted decision has a defined human accountable for it.' ),
			array( 'dim' => 'governance', 'text' => 'We could explain an AI-driven outcome to a regulator or trustee if asked.' ),
			array( 'dim' => 'capability', 'text' => 'Our people have the skills to use AI tools well in their roles.' ),
			array( 'dim' => 'capability', 'text' => 'We have access to the technical capability to deploy and maintain AI systems.' ),
			array( 'dim' => 'capability', 'text' => 'Training is structured and ongoing, not left to individual initiative.' ),
			array( 'dim' => 'culture', 'text' => 'Staff treat AI as a trusted part of how work gets done, not a threat.' ),
			array( 'dim' => 'culture', 'text' => 'Teams share what works with AI rather than keeping it in pockets.' ),
			array( 'dim' => 'culture', 'text' => 'Leadership models AI use rather than only mandating it.' ),
		),
		'bands' => array(
			array( 'min' => 0,  'max' => 40,  'name' => 'Exploring',        'blurb' => 'AI is happening in pockets, without a strategy or guardrails. The priority is to set direction and governance before scale, not after.' ),
			array( 'min' => 41, 'max' => 65,  'name' => 'Experimenting',    'blurb' => 'Useful pilots exist but don’t yet add up to capability. The priority is to turn the strongest pilot into a governed, repeatable practice.' ),
			array( 'min' => 66, 'max' => 85,  'name' => 'Operationalising', 'blurb' => 'AI is becoming part of how work is done, under some governance. The priority is to close the remaining foundation gap and standardise.' ),
			array( 'min' => 86, 'max' => 100, 'name' => 'AI-native',        'blurb' => 'AI is embedded, governed, and trusted across the organisation. The priority is to extend it into higher-value, agentic work.' ),
		),
		'recommendations' => array(
			'strategy'   => array( 'title' => 'Start with direction.', 'body' => 'The foundation to strengthen first is strategy — a documented plan tying AI to commercial outcomes, with a named owner. A transformation roadmap turns scattered pilots into a governed programme.', 'linkText' => 'Explore Digital Transformation', 'url' => '/services/digital/digital-transformation/' ),
			'data'       => array( 'title' => 'Start with the data foundation.', 'body' => 'AI is only as reliable as the data beneath it. The priority is unified, governed, accessible data — a single source of truth the models can stand on.', 'linkText' => 'Explore Data Infrastructure', 'url' => '/services/digital/data-infrastructure/' ),
			'governance' => array( 'title' => 'Start with governance.', 'body' => 'Capability is ahead of control. The priority is an explicit governance posture — bias review, data residency, human override — so the work survives scrutiny from a regulator or trustee.', 'linkText' => 'Explore AI Adoption Programmes', 'url' => '/services/digital/ai-adoption-programmes/' ),
			'capability' => array( 'title' => 'Start with capability.', 'body' => 'The intent is there; the skills and support are not yet. The priority is a structured adoption programme — role-based training and the capability to deploy and maintain.', 'linkText' => 'Explore AI Adoption Programmes', 'url' => '/services/digital/ai-adoption-programmes/' ),
			'culture'    => array( 'title' => 'Start with adoption.', 'body' => 'The tools exist but trust and habit lag. The priority is an adoption programme that builds judgement and shared practice — led from the top, not mandated from it.', 'linkText' => 'Explore Digital Transformation', 'url' => '/services/digital/digital-transformation/' ),
		),
		'reportLines' => $report_lines,
	);

	/* ---------------- T-03 · AI Adoption Assessment — Individual ---------------- */
	$configs['ai-adoption-assessment-individual'] = array(
		'id' => 'ai-adoption-assessment-individual', 'engine' => 'likert', 'code' => 'Diagnostic · T-03',
		'title' => 'AI Adoption Assessment — Individual',
		'lede' => 'Ten statements about how you personally work with AI today. You’ll see where you sit on the fluency curve and what to learn next — instantly, before any email is requested.',
		'startLabel' => 'Begin the assessment', 'scoreLabel' => 'Your AI fluency score',
		'facts' => array( array( 'v' => '4 min', 'k' => 'To complete' ), array( 'v' => '10', 'k' => 'Statements' ), array( 'v' => 'Free', 'k' => 'Instant read-out' ) ),
		'scale' => $scale,
		'dimensions' => array(
			array( 'id' => 'awareness', 'label' => 'Awareness' ),
			array( 'id' => 'skills', 'label' => 'Practical skills' ),
			array( 'id' => 'workflow', 'label' => 'Workflow integration' ),
			array( 'id' => 'judgement', 'label' => 'Judgement & governance' ),
		),
		'questions' => array(
			array( 'dim' => 'awareness', 'text' => 'I understand what today’s AI tools can and can’t do.' ),
			array( 'dim' => 'awareness', 'text' => 'I keep up with how AI is changing the work in my field.' ),
			array( 'dim' => 'awareness', 'text' => 'I can tell the difference between AI hype and genuine capability.' ),
			array( 'dim' => 'skills', 'text' => 'I use AI tools in my daily workflow, not just occasionally.' ),
			array( 'dim' => 'skills', 'text' => 'I can write effective prompts to get useful results.' ),
			array( 'dim' => 'skills', 'text' => 'I can combine AI tools to complete a multi-step task.' ),
			array( 'dim' => 'workflow', 'text' => 'AI is built into my regular workflow, not a separate detour.' ),
			array( 'dim' => 'workflow', 'text' => 'I’ve redesigned at least one routine task around AI.' ),
			array( 'dim' => 'judgement', 'text' => 'I can judge when an AI output is wrong or unsafe to use.' ),
			array( 'dim' => 'judgement', 'text' => 'I know what information I should not put into a public AI tool.' ),
		),
		'bands' => array(
			array( 'min' => 0,  'max' => 40,  'name' => 'Curious', 'blurb' => 'You’re aware of AI but not yet working with it much. The fastest gains come from deliberate, hands-on practice on real tasks.' ),
			array( 'min' => 41, 'max' => 65,  'name' => 'Capable', 'blurb' => 'You use AI usefully but unevenly. The next step is to make it a default part of your workflow rather than an occasional detour.' ),
			array( 'min' => 66, 'max' => 85,  'name' => 'Fluent',  'blurb' => 'AI is part of how you work, with sound judgement about its limits. The next step is to combine tools and lift others around you.' ),
			array( 'min' => 86, 'max' => 100, 'name' => 'Native',  'blurb' => 'You work natively with AI and judge its output well. You’re positioned to lead adoption for your team.' ),
		),
		'recommendations' => array(
			'awareness' => array( 'title' => 'Build your foundation.', 'body' => 'Start with structured awareness — what current tools genuinely do, where they fail, and how they’re changing your field. A guided programme beats scattered reading.', 'linkText' => 'Explore AI Adoption Programmes', 'url' => '/services/digital/ai-adoption-programmes/' ),
			'skills'    => array( 'title' => 'Practise deliberately.', 'body' => 'You know the landscape; now build the hands-on skills — prompting, tool selection, and chaining steps — on the tasks you actually do.', 'linkText' => 'Explore AI Adoption Programmes', 'url' => '/services/digital/ai-adoption-programmes/' ),
			'workflow'  => array( 'title' => 'Redesign a workflow.', 'body' => 'The gap is integration. Pick one routine task and rebuild it around AI end to end — that’s where occasional use becomes compounding capability.', 'linkText' => 'Explore AI Adoption Programmes', 'url' => '/services/digital/ai-adoption-programmes/' ),
			'judgement' => array( 'title' => 'Sharpen your judgement.', 'body' => 'You use AI well; protect that with judgement — knowing when output is wrong or unsafe, and what never to share with a public tool. Governance is the difference between fast and reckless.', 'linkText' => 'Explore AI Adoption Programmes', 'url' => '/services/digital/ai-adoption-programmes/' ),
		),
		'reportLines' => array( 'Your fluency profile across all four areas', 'A suggested learning path for the next 90 days', 'Where a team rollout would compound your progress' ),
	);

	/* ---------------- T-04 · Brand Authority Audit ---------------- */
	$configs['brand-authority-audit'] = array(
		'id' => 'brand-authority-audit', 'engine' => 'likert', 'code' => 'Diagnostic · T-04',
		'title' => 'Brand Authority Audit',
		'lede' => 'Twelve statements across the four drivers of brand authority — visibility, credibility, consistency, and share of voice. You’ll see your score and the weakest driver before any email is requested.',
		'startLabel' => 'Begin the audit', 'scoreLabel' => 'Your brand authority score',
		'facts' => array( array( 'v' => '5 min', 'k' => 'To complete' ), array( 'v' => '12', 'k' => 'Statements' ), array( 'v' => 'Free', 'k' => 'Instant read-out' ) ),
		'scale' => $scale,
		'dimensions' => array(
			array( 'id' => 'visibility', 'label' => 'Visibility' ),
			array( 'id' => 'credibility', 'label' => 'Credibility' ),
			array( 'id' => 'consistency', 'label' => 'Consistency' ),
			array( 'id' => 'sov', 'label' => 'Share of voice' ),
		),
		'questions' => array(
			array( 'dim' => 'visibility', 'text' => 'Decision-makers in our market recognise our name unprompted.' ),
			array( 'dim' => 'visibility', 'text' => 'When buyers describe our category, we come to mind early.' ),
			array( 'dim' => 'visibility', 'text' => 'We show up where our buyers are already looking for answers.' ),
			array( 'dim' => 'credibility', 'text' => 'Our executives are quoted or cited in the conversations that matter.' ),
			array( 'dim' => 'credibility', 'text' => 'We have third-party proof — references, credentials, or coverage — that buyers trust.' ),
			array( 'dim' => 'credibility', 'text' => 'Our claims are backed by evidence, not adjectives.' ),
			array( 'dim' => 'consistency', 'text' => 'Our messaging is consistent across every channel and spokesperson.' ),
			array( 'dim' => 'consistency', 'text' => 'Someone encountering us twice would recognise the same brand.' ),
			array( 'dim' => 'consistency', 'text' => 'Our visual and verbal identity is documented and followed.' ),
			array( 'dim' => 'sov', 'text' => 'We are present in the industry conversations that shape buying decisions.' ),
			array( 'dim' => 'sov', 'text' => 'We publish more useful material than our direct competitors.' ),
			array( 'dim' => 'sov', 'text' => 'Our point of view is distinct, not interchangeable with others.' ),
		),
		'bands' => array(
			array( 'min' => 0,  'max' => 40,  'name' => 'Invisible',     'blurb' => 'The brand isn’t yet registering with the people who matter. The priority is presence — showing up, usefully, where buyers already look.' ),
			array( 'min' => 41, 'max' => 65,  'name' => 'Known',         'blurb' => 'You’re recognised but not yet relied on. The priority is credibility and consistency — turning awareness into trust.' ),
			array( 'min' => 66, 'max' => 85,  'name' => 'Respected',     'blurb' => 'You’re trusted in your space. The priority is share of voice — leading the conversations that shape decisions.' ),
			array( 'min' => 86, 'max' => 100, 'name' => 'Authoritative', 'blurb' => 'You set the agenda in your category. The priority is to defend and widen that lead.' ),
		),
		'recommendations' => array(
			'visibility'  => array( 'title' => 'Start with presence.', 'body' => 'The weakest driver is visibility. The priority is useful, findable content where buyers already search — so the brand starts registering with the people who matter.', 'linkText' => 'Explore Content Development', 'url' => '/services/market/content-development/' ),
			'credibility' => array( 'title' => 'Start with credibility.', 'body' => 'You’re seen but not yet relied on. The priority is executive visibility and proof — a thought-leadership programme that puts your people in the conversations that confer trust.', 'linkText' => 'Explore Thought-Leadership Programmes', 'url' => '/services/market/thought-leadership-programmes/' ),
			'consistency' => array( 'title' => 'Start with consistency.', 'body' => 'The message changes by channel and spokesperson. The priority is an institutional brand system — documented identity and messaging that holds everywhere.', 'linkText' => 'Explore Institutional Brand & Reputation', 'url' => '/services/market/institutional-brand-and-reputation/' ),
			'sov'         => array( 'title' => 'Start with share of voice.', 'body' => 'You’re respected but quiet. The priority is a content engine that makes you the most useful voice in the conversations that shape buying decisions.', 'linkText' => 'Explore Content Development', 'url' => '/services/market/content-development/' ),
		),
		'reportLines' => $report_lines,
	);

	/* ---------------- T-05 · Revenue Leakage Calculator (variant: calculator) ---------------- */
	$configs['revenue-leakage-calculator'] = array(
		'id' => 'revenue-leakage-calculator', 'engine' => 'calculator', 'code' => 'Calculator · T-05',
		'title' => 'Revenue Leakage Calculator',
		'lede' => 'Six numbers about your commercial engine return an estimate of the revenue leaking each year — across conversion, retention, pricing, and expansion. An estimate from your inputs, not a benchmark claim.',
		'startLabel' => 'Enter your numbers', 'scoreLabel' => 'Estimated annual leakage',
		'stepHeading' => 'Tell us about your commercial engine.',
		'estimateNote' => 'These figures produce an estimate from your own inputs against conservative reference points — not a measurement of your business and not a benchmark against others. Treat it as a direction, not a number to bank.',
		'figureCaption' => 'Estimated revenue recoverable each year, from your inputs. An estimate, not a measurement — we’d confirm it properly in a scoping call.',
		'facts' => array( array( 'v' => '3 min', 'k' => 'To complete' ), array( 'v' => '6', 'k' => 'Inputs' ), array( 'v' => 'Estimate', 'k' => 'From your numbers' ) ),
		'inputs' => array(
			array( 'id' => 'revenue', 'label' => 'Annual revenue (₦)', 'default' => 100000000 ),
			array( 'id' => 'dealValue', 'label' => 'Average deal / customer value (₦)', 'default' => 500000 ),
			array( 'id' => 'conv', 'label' => 'Lead → close conversion (%)', 'default' => 20 ),
			array( 'id' => 'churn', 'label' => 'Monthly customer churn (%)', 'default' => 4 ),
			array( 'id' => 'expansion', 'label' => 'Customers on an expansion motion (%)', 'default' => 10 ),
			array( 'id' => 'discount', 'label' => 'Average discount given (%)', 'default' => 12 ),
		),
		'benchmarks' => array(
			'benchmarkConv' => 30, // reference lead→close %
			'targetChurn' => 2,    // reference monthly churn %
			'floorDiscount' => 5,  // reference acceptable discount %
			'targetExpansion' => 30, // reference % of customers on expansion
		),
		'recommendations' => array(
			'conversion' => array( 'title' => 'The biggest leak is conversion.', 'body' => 'Most of the estimated leakage sits between a qualified lead and a signed deal. The priority is the commercial spine — process, playbooks, and pricing discipline.', 'linkText' => 'Explore B2B Sales Enablement', 'url' => '/services/market/b2b-sales-enablement/' ),
			'retention'  => array( 'title' => 'The biggest leak is retention.', 'body' => 'Most of the estimated leakage is churn. The priority is retention and lifecycle economics — modelled and improved against evidence.', 'linkText' => 'Explore Revenue Optimisation', 'url' => '/services/digital/revenue-optimisation/' ),
			'pricing'    => array( 'title' => 'The biggest leak is pricing.', 'body' => 'Discounting is quietly eroding margin. The priority is pricing and packaging discipline — deliberate, defended, and measured.', 'linkText' => 'Explore Revenue Optimisation', 'url' => '/services/digital/revenue-optimisation/' ),
			'expansion'  => array( 'title' => 'The biggest gap is expansion.', 'body' => 'You’re leaving growth on the table with existing customers. The priority is a deliberate expansion motion — the cheapest revenue you have.', 'linkText' => 'Explore Revenue Optimisation', 'url' => '/services/digital/revenue-optimisation/' ),
			'default'    => array( 'title' => 'Start with revenue you already have.', 'body' => 'The priority is to model and seal the leaks — pricing, retention, conversion, and expansion — against evidence rather than instinct.', 'linkText' => 'Explore Revenue Optimisation', 'url' => '/services/digital/revenue-optimisation/' ),
		),
		'reportLines' => array( 'Your leakage estimate broken down by component', 'The assumptions behind each figure, so you can pressure-test them', 'A sequenced plan to seal the largest leaks first' ),
	);

	/* ---------------- T-06 · Marketing Maturity Scorecard ---------------- */
	$configs['marketing-maturity-scorecard'] = array(
		'id' => 'marketing-maturity-scorecard', 'engine' => 'likert', 'code' => 'Diagnostic · T-06',
		'title' => 'Marketing Maturity Scorecard',
		'lede' => 'Fifteen statements across the five parts of a marketing system — strategy, channels, data, automation, and content. You’ll see your score and the weakest part before any email is requested.',
		'startLabel' => 'Begin the scorecard', 'scoreLabel' => 'Your marketing maturity score',
		'facts' => array( array( 'v' => '5 min', 'k' => 'To complete' ), array( 'v' => '15', 'k' => 'Statements' ), array( 'v' => 'Free', 'k' => 'Instant read-out' ) ),
		'scale' => $scale,
		'dimensions' => array(
			array( 'id' => 'strategy', 'label' => 'Strategy' ),
			array( 'id' => 'channels', 'label' => 'Channels' ),
			array( 'id' => 'data', 'label' => 'Data & measurement' ),
			array( 'id' => 'automation', 'label' => 'Automation' ),
			array( 'id' => 'content', 'label' => 'Content' ),
		),
		'questions' => array(
			array( 'dim' => 'strategy', 'text' => 'Our marketing has a documented strategy tied to revenue targets.' ),
			array( 'dim' => 'strategy', 'text' => 'We know which segments we’re pursuing and why.' ),
			array( 'dim' => 'strategy', 'text' => 'Marketing and sales agree on what a qualified lead is.' ),
			array( 'dim' => 'channels', 'text' => 'We invest in a deliberate channel mix, not whatever is loudest.' ),
			array( 'dim' => 'channels', 'text' => 'Our channels work together rather than in silos.' ),
			array( 'dim' => 'channels', 'text' => 'We can scale a channel up when it’s working.' ),
			array( 'dim' => 'data', 'text' => 'We can attribute pipeline to specific marketing activity.' ),
			array( 'dim' => 'data', 'text' => 'We measure marketing against revenue, not vanity metrics.' ),
			array( 'dim' => 'data', 'text' => 'Our reporting is trusted enough to make budget decisions on.' ),
			array( 'dim' => 'automation', 'text' => 'Our lifecycle is automated on a unified view of the customer.' ),
			array( 'dim' => 'automation', 'text' => 'Repetitive marketing tasks are automated, not manual.' ),
			array( 'dim' => 'automation', 'text' => 'Leads are routed and nurtured without someone remembering to do it.' ),
			array( 'dim' => 'content', 'text' => 'We publish content engineered for our buyers, not for us.' ),
			array( 'dim' => 'content', 'text' => 'Our content compounds — older pieces still generate value.' ),
			array( 'dim' => 'content', 'text' => 'Content is planned against the buying journey, not produced ad hoc.' ),
		),
		'bands' => $ladder_bands,
		'recommendations' => array(
			'strategy'   => array( 'title' => 'Start with strategy.', 'body' => 'The weakest part is the plan itself. The priority is a documented demand strategy — segments, targets, and a shared definition of a qualified lead.', 'linkText' => 'Explore Demand Generation', 'url' => '/services/market/demand-generation/' ),
			'channels'   => array( 'title' => 'Start with the channel system.', 'body' => 'Channels are working in isolation. The priority is a deliberate, integrated mix engineered for predictable pipeline rather than scattered activity.', 'linkText' => 'Explore Demand Generation', 'url' => '/services/market/demand-generation/' ),
			'data'       => array( 'title' => 'Start with measurement.', 'body' => 'You can’t yet tie spend to revenue. The priority is attribution and reporting trustworthy enough to make budget decisions on.', 'linkText' => 'Explore Marketing Automation', 'url' => '/services/market/marketing-automation/' ),
			'automation' => array( 'title' => 'Start with automation.', 'body' => 'Too much runs on manual effort and memory. The priority is lifecycle automation on a unified view of the customer.', 'linkText' => 'Explore Marketing Automation', 'url' => '/services/market/marketing-automation/' ),
			'content'    => array( 'title' => 'Start with content.', 'body' => 'Content isn’t yet compounding. The priority is a content engine planned against the buying journey and engineered for citation.', 'linkText' => 'Explore Content Development', 'url' => '/services/market/content-development/' ),
		),
		'reportLines' => $report_lines,
	);

	/* ---------------- T-07 · Automation Opportunity Finder (variant: finder) ---------------- */
	$configs['automation-opportunity-finder'] = array(
		'id' => 'automation-opportunity-finder', 'engine' => 'finder', 'code' => 'Finder · T-07',
		'title' => 'Automation Opportunity Finder',
		'lede' => 'Pick the workflow areas that drain your team, rate each for frequency and pain, and we’ll rank the opportunities with the highest return — before any email is requested.',
		'startLabel' => 'Find your opportunities', 'scoreLabel' => 'Your highest-return opportunities',
		'selectHeading' => 'Which of these workflows drain time today?',
		'qualifyHeading' => 'For each, how often does it happen — and how painful is it?',
		'resultHeading' => 'Start with these.',
		'facts' => array( array( 'v' => '4 min', 'k' => 'To complete' ), array( 'v' => 'Ranked', 'k' => 'By impact' ), array( 'v' => 'Free', 'k' => 'Instant read-out' ) ),
		'areas' => array(
			array( 'id' => 'lead-routing', 'label' => 'Lead routing & assignment', 'method' => 'Rules-based routing on a single source of truth, so no lead waits on a human to notice it.', 'effort' => 'Low', 'service' => 'automation-revops' ),
			array( 'id' => 'reporting', 'label' => 'Reporting & dashboards', 'method' => 'Automated data pipelines into a live dashboard, replacing the weekly manual rebuild.', 'effort' => 'Low', 'service' => 'automation-revops' ),
			array( 'id' => 'onboarding', 'label' => 'Customer or staff onboarding', 'method' => 'A sequenced workflow with task automation and reminders, so nothing is dropped.', 'effort' => 'Medium', 'service' => 'automation-revops' ),
			array( 'id' => 'invoicing', 'label' => 'Invoicing & billing', 'method' => 'Triggered invoicing from your system of record, with exceptions flagged for review.', 'effort' => 'Medium', 'service' => 'automation-revops' ),
			array( 'id' => 'support-triage', 'label' => 'Support triage', 'method' => 'An agent that classifies and routes incoming requests, with a human-in-the-loop on edge cases.', 'effort' => 'Medium', 'service' => 'ai-agent-deployment' ),
			array( 'id' => 'data-entry', 'label' => 'Data entry & enrichment', 'method' => 'Automatic capture and enrichment of records, removing the copy-paste tax.', 'effort' => 'Low', 'service' => 'data-infrastructure' ),
			array( 'id' => 'scheduling', 'label' => 'Scheduling & reminders', 'method' => 'Automated scheduling and follow-up nudges against your calendar and CRM.', 'effort' => 'Low', 'service' => 'automation-revops' ),
			array( 'id' => 'document-handling', 'label' => 'Document handling', 'method' => 'Extraction and summarisation of documents, with a human check before anything commits.', 'effort' => 'Medium', 'service' => 'ai-agent-deployment' ),
		),
		'recommendations' => array(
			'default' => array( 'title' => 'Start with RevOps automation.', 'body' => 'Your highest-return opportunities are operational workflows. The priority is to build them on a governed data model — automation that your team can read, audit, and change.', 'linkText' => 'Explore Automation & RevOps', 'url' => '/services/digital/automation-and-revops/' ),
			'agentic' => array( 'title' => 'Start with an agent.', 'body' => 'Your top opportunity is a judgement task an agent can take on — with a human-in-the-loop. The priority is governed agent deployment, not a brittle macro.', 'linkText' => 'Explore AI Agent Deployment', 'url' => '/services/digital/ai-agent-deployment/' ),
			'data' => array( 'title' => 'Start with the data foundation.', 'body' => 'Your top opportunity depends on clean, accessible data. The priority is the data infrastructure underneath — automate on a foundation, not on sand.', 'linkText' => 'Explore Data Infrastructure', 'url' => '/services/digital/data-infrastructure/' ),
		),
		'reportLines' => array( 'Your full ranked list of opportunities', 'A one-line method and effort estimate for each', 'A sequenced plan starting with the highest return' ),
	);

	/* ---------------- T-08 · Partnership Readiness Quiz ---------------- */
	$configs['partnership-readiness-quiz'] = array(
		'id' => 'partnership-readiness-quiz', 'engine' => 'likert', 'code' => 'Diagnostic · T-08',
		'title' => 'Partnership Readiness Quiz',
		'lede' => 'Nine statements across the four conditions for landing institutional partnerships — mandate, value, structure, and relationships. You’ll see your readiness before any email is requested.',
		'startLabel' => 'Begin the quiz', 'scoreLabel' => 'Your partnership readiness',
		'facts' => array( array( 'v' => '3 min', 'k' => 'To complete' ), array( 'v' => '9', 'k' => 'Statements' ), array( 'v' => 'Free', 'k' => 'Instant read-out' ) ),
		'scale' => $scale,
		'dimensions' => array(
			array( 'id' => 'mandate', 'label' => 'Mandate' ),
			array( 'id' => 'value', 'label' => 'Value & offer' ),
			array( 'id' => 'structure', 'label' => 'Structure & governance' ),
			array( 'id' => 'relationships', 'label' => 'Relationships' ),
		),
		'questions' => array(
			array( 'dim' => 'mandate', 'text' => 'We have leadership mandate and budget to pursue a partnership.' ),
			array( 'dim' => 'mandate', 'text' => 'A senior owner is accountable for partnership outcomes.' ),
			array( 'dim' => 'mandate', 'text' => 'We have the capacity to deliver on a partnership if we land one.' ),
			array( 'dim' => 'value', 'text' => 'We can articulate a clear value exchange for the other party.' ),
			array( 'dim' => 'value', 'text' => 'We know what we want from a partnership and what we’ll give.' ),
			array( 'dim' => 'structure', 'text' => 'We have a process for governing a multi-stakeholder agreement.' ),
			array( 'dim' => 'structure', 'text' => 'We can manage the legal, commercial, and delivery sides of a deal.' ),
			array( 'dim' => 'relationships', 'text' => 'We have warm access to the partners we want to reach.' ),
			array( 'dim' => 'relationships', 'text' => 'We have a track record that makes serious partners take us seriously.' ),
		),
		'bands' => array(
			array( 'min' => 0,  'max' => 45,  'name' => 'Not yet',             'blurb' => 'The foundations for a serious partnership aren’t in place yet. Pushing now risks a deal you can’t govern or deliver. Build the base first.' ),
			array( 'min' => 46, 'max' => 75,  'name' => 'Foundations in place', 'blurb' => 'You have real ingredients but gaps remain. Close the weakest condition before you take a partnership to a serious counterparty.' ),
			array( 'min' => 76, 'max' => 100, 'name' => 'Ready to partner',     'blurb' => 'You have the mandate, value, structure, and relationships to pursue partnerships with confidence. The priority is to move deliberately on the right ones.' ),
		),
		'recommendations' => array(
			'mandate'       => array( 'title' => 'Secure the mandate first.', 'body' => 'Without clear leadership mandate, budget, and delivery capacity, a partnership stalls. Begin with advisory framing to build the internal case before approaching counterparties.', 'linkText' => 'Explore Strategic Partnerships', 'url' => '/services/market/strategic-partnerships/' ),
			'value'         => array( 'title' => 'Sharpen the value exchange.', 'body' => 'A partnership needs a value exchange both sides can name. The priority is to define what you offer and seek, clearly enough to put in front of a serious partner.', 'linkText' => 'Explore Strategic Partnerships', 'url' => '/services/market/strategic-partnerships/' ),
			'structure'     => array( 'title' => 'Build the governance.', 'body' => 'The gap is structure — governing a multi-stakeholder agreement across legal, commercial, and delivery. The priority is a deal structure and governance you can stand behind.', 'linkText' => 'Explore Strategic Partnerships', 'url' => '/services/market/strategic-partnerships/' ),
			'relationships' => array( 'title' => 'Open the relationships.', 'body' => 'The ingredients are there but access isn’t. The priority is warm, credible routes to the partners you want — and the track record that makes them engage.', 'linkText' => 'Explore Strategic Partnerships', 'url' => '/services/market/strategic-partnerships/' ),
		),
		'reportLines' => array( 'Your readiness across all four conditions', 'The specific gaps to close before approaching a partner', 'A sequenced path from foundations to first deal' ),
	);

	/* ---------------- Paid audit tools (variant engine: audit) ---------------- */
	$configs['social-media-audit'] = array(
		'id' => 'social-media-audit', 'engine' => 'audit', 'subtype' => 'social', 'code' => 'Audit · T-09 · Paid',
		'title' => 'Social Media Audit',
		'lede' => 'A real audit of your brand’s social presence — which platforms you’re on, whether each profile is reachable and complete, whether your website connects them, and how consistent your handle is. Free scored report; unlock the detailed, platform-by-platform fix list for $10.',
		'startLabel' => 'Start the audit', 'scoreLabel' => 'Your social presence score',
		'price' => 10, 'currency' => 'USD', 'priceLabel' => '$10',
		'facts' => array( array( 'v' => '4 min', 'k' => 'To complete' ), array( 'v' => 'Live', 'k' => 'Real checks' ), array( 'v' => '$10', 'k' => 'Detailed fixes' ) ),
		'inputs' => array(
			array( 'id' => 'website', 'type' => 'url', 'label' => 'Your website (optional)', 'placeholder' => 'https://yourbrand.com' ),
			array( 'id' => 'socials', 'type' => 'urls', 'label' => 'Your social profile links', 'placeholder' => 'https://instagram.com/yourbrand' ),
		),
		'reportLines' => array( 'Every platform we checked, and what’s missing', 'A prioritised, platform-by-platform fix list', 'Quick wins you can action this week' ),
	);

	$configs['digital-presence-orm-audit'] = array(
		'id' => 'digital-presence-orm-audit', 'engine' => 'audit', 'subtype' => 'orm', 'code' => 'Audit · T-10 · Paid',
		'title' => 'Digital Presence & ORM Audit',
		'lede' => 'A full read on how your brand shows up online — website, SEO, social presence, and reputation — scored across four pillars from real checks on your live properties. Free scored report; unlock the detailed remediation plan for $20.',
		'startLabel' => 'Start the audit', 'scoreLabel' => 'Your digital presence score',
		'price' => 20, 'currency' => 'USD', 'priceLabel' => '$20',
		'facts' => array( array( 'v' => '5 min', 'k' => 'To complete' ), array( 'v' => '4 pillars', 'k' => 'Scored' ), array( 'v' => '$20', 'k' => 'Full plan' ) ),
		'inputs' => array(
			array( 'id' => 'website', 'type' => 'url', 'label' => 'Your website', 'placeholder' => 'https://yourbrand.com', 'required' => true ),
			array( 'id' => 'socials', 'type' => 'urls', 'label' => 'Your social profile links (optional)', 'placeholder' => 'https://facebook.com/yourbrand' ),
			array( 'id' => 'google_rating', 'type' => 'number', 'label' => 'Your Google rating, 0–5 (optional)', 'placeholder' => '4.6', 'step' => '0.1', 'min' => '0', 'max' => '5' ),
			array( 'id' => 'google_reviews', 'type' => 'number', 'label' => 'Your Google review count (optional)', 'placeholder' => '120', 'step' => '1', 'min' => '0' ),
		),
		'reportLines' => array( 'Every check across website, SEO, social and reputation', 'A prioritised remediation plan (high → low impact)', 'The evidence behind each score' ),
	);

	return $configs;
}
