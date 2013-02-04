<?php
// =======================================================
// 	POSITORY - THE NEGATIVE NEWS SUPPOSITORY
//  DATE: 24/02/2010
//  URL: http://www.toggle.uk.com
// =======================================================

// include SPF
include('includes/master.inc.php');

// page generation time start
$start = round(microtime(), 4); 
    
// turn error reporting on or off
ini_set('display_errors', '0');

// check if we are querying a URL
if (isset($_GET['q'])) {

	// our words and sentances
	$super_good = array('congratulations','sex','sexy','brightens up', 'brighten up','inspire','inspirational','inspiration','miricale','spectacular','amazing','delightful','paradise','rejoice','cure','victory','speclative','pository');
	$good = array('free','win','winner','sun','sunny','good','home','reduced','more','best','better','positive','happy','incredible','award','boost','enjoy','exceptional','kind','kindness','prefect','special','wow','up','exciting','excited','new','friend','friendly','joy','relax','rest','sweet','brilliant','awesome','ace','healthy','pleasure','rewarding','sensational','wonderful','cool','fun','great','impressive','light','safe','stable','help','helpful','helping','charity','donate','donating','sponsor','sponsorship','justice','right','played well','sporting','polite','prefect','successful','success'.'succulent','true','born','birth','marriage','wedding','partnership','appeal','sentance','sentanced','animals','rescue','rescued','heart','prizes','prize','create','creative','love','loving','lucky','fortunate','puppy','kitten','nominated','nominations','dream');
	$bad = array('suicide','death','dead','brutally','bad','negative','sad','fight','evil','down','recession','redundant','redundancy','depressed','depression','old','sour','unhealthy','pain','pained','costly','dark','unsafe','regulations','oppressed','oppression','desperate','conflict','reckless','tax','spin','dejected','unconscious', 'extinction','critical','serious','warning','prison','assult','pollution','crime','criminal','thug','chav','steal','stole','stealing','knife','damage','damaged','burglary','burgled','wrong','attacker','attack','worse','worst','insolvent','extremist','scare','scared','cancer','anger','false','sobbing','humiliated','cost-cutting','cuts','loss','losses','distress','detained','suspects','drugs','cocaine','overdose','misconduct','inappropriately','inappropriate','convicted','scandals','sobbing','asbo','binge','alcoholism','hostage','pandemic','epidemic','hijack','hijacked','hi-jack','threat','threaten','threatens','extinct','outcry','hysteria','degradation','tabloid','criticised','shock','shocking','lack','lacks','lacking','victim','victims','nightmare','crisis','looters','looting','vandalism','collapsed','mistake','racist','unemployment','unemployed'); 
	$super_bad = array('killed','kill','killing','murder','murdered','rape','torture','tortured','bomb','gun','explosion','war','famine','catastrophic','stabbed','incureable','terrorist','terrorism','suffer','suffering','masacre','earthquake');

	// increase memory limit - this is an intensive process
	ini_set("memory_limit","40M");

	// if the URL is not valid return an error or redirect
	if (!validate_url(urldecode($_GET['q']))) { print "ERROR: Invalid URL. We could not validate this URL - (1)."; exit(); } 
	if (!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', urldecode($_GET['q']))) {echo "ERROR: Invalid URL. We could not validate this URL - (2)."; exit(); } 	

	// sanitize the URL 
	$url = filter_var(urldecode($_GET['q']), FILTER_SANITIZE_URL);

	// fix slashes
	$url = unslash($url);
		
	// check for a matching URL in the database if found return score else create score
	$s = new Score();
	if ($s->select($url, 'url')) {
		
		// grab the updated time
		$updated	= strtotime($s->updated);
		
		// grab the number of views
		$views 		= intval($s->count);

		// if the link was last updated over 3 hours ago then re-filter it (pages sometimes change - e.g. BBC homepage)
		if ((time() - $updated) >= 10800) {
		
			// load the simple dom class
			$html = new simple_html_dom($url);
			
			// grab the page title for saving to the database
			$title = filter_var($html->find('title', 0), FILTER_SANITIZE_STRING);
					
			// get the contents of the url as plain text
			$html = $html->plaintext;
			
			// remove whitespace in html
			$html = preg_replace('/\s\s+/', ' ', $html);
			
			// if no html is returned then it might have been a dead URL all along		
			if (!$html) { print "ERROR: Unable to retrieve HTML from this page - (3)."; exit(); }

			// get our counts
			$super_good_count = substr_count_array($html,$super_good);
			$good_count = substr_count_array($html,$good);
			$bad_count = substr_count_array($html,$bad);
			$super_bad_count = substr_count_array($html,$super_bad);
			
			// calculate scores
			$good_score = (($super_good_count*2)+($good_count));
			$bad_score = (($super_bad_count*2)+($bad_count));
			$score = ($good_score - $bad_score);
			
			// rank for quick DB search
			if (($good_score == 0) && ($bad_score == 0)) {
				$rank = 0;
			} else if ($score < 6) {
				$rank = 1;
			} else if (($score >= 6) && ($score <= 15)) {
				$rank = 2;
			} else {
				$rank = 3;
			}
			
			// set for updated information
			$s->ip  		= $_SERVER['REMOTE_ADDR'];
			$s->title		= preg_replace('/\s\s+/', ' ', $title);
			$s->goodscore  	= $good_score;
			$s->badscore  	= $bad_score;
			$s->score 		= $score;
			$s->rank		= $rank;
		
		// else grab out of the database
		} else {
				
			$good_score = intval($s->goodscore);
			$bad_score 	= intval($s->badscore);
			$score 		= intval($s->score);
			$rank 		= intval($s->rank); 
			$title		= $s->title;
		
		}
		
		// update changed info
		$s->count = $views+1;
		$s->updated = date('Y-m-d H:i:s');			
		$s->update();
		
	} else {
	
		// load the simple dom class
		$html = new simple_html_dom($url);
		
		
		// grab the page title for saving to the database
		$title = filter_var($html->find('title', 0), FILTER_SANITIZE_STRING);
				
		// get the contents of the url as plain text
		$html = $html->plaintext;
		
		// remove whitespace in html
		$html = preg_replace('/\s\s+/', ' ', $html);
		
		// if no html is returned then it might have been a dead URL all along		
		if (!$html) { print "ERROR: Unable to retrieve HTML from this page - (3)."; exit(); }
	
		// get our counts
		$super_good_count = substr_count_array($html,$super_good);
		$good_count = substr_count_array($html,$good);
		$bad_count = substr_count_array($html,$bad);
		$super_bad_count = substr_count_array($html,$super_bad);
		
		// calculate scores
		$good_score = (($super_good_count*2)+($good_count));
		$bad_score = (($super_bad_count*2)+($bad_count));
		$score = ($good_score - $bad_score);
		
		// rank for quick DB search
		if (($good_score == 0) && ($bad_score == 0)) {
			$rank = 0;
		} else if ($score < 6) {
			$rank = 1;
		} else if (($score >= 6) && ($score <= 15)) {
			$rank = 2;
		} else {
			$rank = 3;
		}
		
		// work out the domain name of URL
		$purl = parse_url($url);
			
		// insert score into database
		$s->time		= date('Y-m-d H:i:s');
		$s->ip  		= $_SERVER['REMOTE_ADDR'];
		$s->count		= 1;
		$s->url      	= $url;
		$s->title		= preg_replace('/\s\s+/', ' ', $title);
		$s->domain      = $purl['host'];
		$s->goodscore  	= $good_score;
		$s->badscore  	= $bad_score;
		$s->score 		= $score;
		$s->rank		= $rank;
		$s->insert();
	
	}
	
	// add to overall counter
	$c = new Counter();
	if ($c->select(1, 'id')) {
		$count = $c->count;
		$c->count = $count+1;
		$c->update();
	}
	
	if (($good_score == 0) && ($bad_score == 0)) {
	
		$output = array('score' => $score, 'rank' => $rank, 'feedback' => '<strong>Hmmmmm.</strong> We are unable to correctly diagnose this page, it may not contain many words.', 'icon' => 'icon-unknown.png');
	
	} else if ($rank == 1) {

		$output = array('score' => $score, 'rank' => $rank, 'feedback' => '<strong>Failed.</strong> This page is absolutely miserable. Perhaps you would feel better if you read something else.', 'icon' => 'icon-bad.png');
		
	} else if ($rank == 2) {
	
		$output = array('score' => $score, 'rank' => $rank, 'feedback' => '<strong>Neutral.</strong> We are unable to prescribe a recommendation for this page. Why not give it a read and send us <a href="http://pository.scott.ee/#feedback" title="Pository Feedback">your feedback</a>.', 'icon' => 'icon-neutral.png');
	
	} else if ($rank == 3) {
	
		$output = array('score' => $score, 'rank' => $rank, 'feedback' => '<strong>Passed.</strong> This page contains positive content. Smile and enjoy!', 'icon' => 'icon-good.png');
	
	}
	
	// output JS header
    header('Content-Type: application/x-javascript; charset=utf8');
    
	// return JSON for bookmarklet
	echo $_GET['callback'] . '(' . json_encode($output) . ');';
	
	// clean up memory
    unset($html);
    unset($url);
    unset($title);
    exit();

} else {
	
	ob_start();

?>
<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>

	<!-- meta -->
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta name="revisit-after" content="7 days">
	<meta name="expires" content="never">
	<meta name="coverage" content="Worldwide">
	<meta name="distribution" content="Global">
	<meta name="identifier-url" content="http://pository.scott.ee">
	<meta name="home_url" content="http://pository.scott.ee">
	<meta name="company" content="Scott Evans">
	<meta name="author" content="Scott - scott.ee">
	<meta name="copyright" content="Scott - scott.ee">
	
	<!-- title -->
	<title>Pository - The negative news suppository (Bookmarklet)</title>
	<meta name="description" content="Are you fed up of the &lsquo;doom and gloom&rsquo; that is so prolific in our daily news? Make your day a happier one with Pository &reg; : An Internet negativity filter (delivered via bookmarklet), that determines if an online article is worth reading (or not).">
	<meta name="keywords" content="positive, pository, suppository, news, filter, bookmarklet, medication, blogs, online, journalism, time, save, negative, unhappy, miserable, happy, positive">
		
	<!-- favicon -->
	<link rel="shortcut icon" type="image/png" href="/favicon.png">
	
	<!-- css -->
	<link rel="stylesheet" type="text/css" href="/combine.php?type=css&amp;files=reset.css,screen.css">

	<!-- enable html5 in <= IE8 -->
	<!--[if lte IE 8]><script src="/js/html5.js"></script><![endif]-->
	
	<!-- <= IE7 css -->
	<!--[if lte IE 7]><link rel="stylesheet" type="text/css" href="/styles/ie.css"><![endif]-->

	<!-- flattr -->
	<script type="text/javascript">
	    (function() {
	        var s = document.createElement('script'), t = document.getElementsByTagName('script')[0];
	        
	        s.type = 'text/javascript';
	        s.async = true;
	        s.src = 'http://api.flattr.com/js/0.5.0/load.js?mode=auto';
	        
	        t.parentNode.insertBefore(s, t);
	    })();
	</script>
	
</head>
<body>
	<div id="container">
	
		<nav id="skip">
			<ul>
		    	<li><a href="#nav" title="Skip to the navigation &raquo;">Skip to the navigation &raquo;</a></li>
		        <li><a href="#content" title="Skip to the main content &raquo;">Skip to main content &raquo;</a></li>
		    </ul>
		</nav>
		<!-- #skip -->
	
		<section id="bookmarklet">
			<a href="javascript:(function(){positoryjs=document.createElement('SCRIPT');positoryjs.type='text/javascript';positoryjs.src='http://pository.scott.ee/js/pository.js';document.getElementsByTagName('head')[0].appendChild(positoryjs);})();" title="Pository" class="replace" id="bookmarklet-pill">Pository<span></span></a>
			<p>Drag the suppository to your bookmarks toolbar</p>
		</section>
		<!-- #bookmarklet -->
		
		<div id="content-container">
			
			<section id="content">
			
				<header id="header">
					<h1><a href="http://pository.scott.ee/" title="Pository - The negative news suppository" id="logo" class="replace">Pository<span></span></a></h1>
					<h2>The negative news suppository</h2>
					<p id="introduction" class="replace">Are you fed up of the &lsquo;doom and gloom&rsquo; that is so prolific in our daily news? Make your day a happier one with Pository &reg; : An Internet negativity filter (delivered via bookmarklet), that determines if an online article is worth reading (or not).<span></span></p>
				</header>
				<!-- header -->
				
				<!-- slider begin -->
				<div id="slider">
					<div id="slider-container">
					
						<article id="home" class="panel">
							<div id="label">
								<p>Prescribed to: <?php print $_SERVER['REMOTE_ADDR'];?>.</p>
								<p>Take as required. Insert into back passage.</p>
								<p>Consult your GP (<a href="#feedback" title="Pository Feedback">Scott Evans</a>) if problems persist.</p>
							</div>
							<!-- #label -->
							<span id="home-capsules" class="home-type">8 Suppositories</span>
							<span id="home-dose" class="home-type">100mg</span>
						</article>
						<!-- #home -->
						
						<article id="help" class="panel">
							<h3>Help</h3>
							<h4>What is a bookmarklet?</h4>
							<p>A bookmarklet is a small JavaScript application stored as a bookmark in your web browser. You can find out more about bookmarklets on <a href="http://en.wikipedia.org/wiki/Bookmarklet" title="Bookmarklet">Wikipedia</a>. </p>
							<h4>How do I install the Pository bookmarklet?</h4>
							<p>You need to drag the green suppository at the top of the screen into your browsers bookmark toolbar. <a href="http://www.youtube.com/watch?v=QrwevUN0KdQ" title="Install a Bookmarklet">Google have produced a video</a> detailing this process.</p>
							<h4>How do I use it?</h4>
							<p>Visit a web page and click the Pository bookmarklet. We will analyse the page and send back our feedback. You will instantly know whether or not the site/article is worth reading.</p>
							<h4>Do you track my browsing habits?</h4>
							<p>Nope, we do not track your browsing habits. Pages are analysed anonymously.</p>
						</article>
						<!-- #help -->
						
						<article id="trends" class="panel">
							<h3>Trends</h3>
							<h4>Coming soon&hellip;</h4>
							<p>As this is a new service we are still busy collecting data. Once we have enough we will launch a real-time trends visualisation. We will be tracking things like:</p>
							<ul>
								<li>Best and worst news stories for negative news.</li>
								<li>Best and worst domains. Perhaps one new source is particularly miserable?</li>
								<li>Popular news stories.</li>
								<li>Real-time information for sites tested (those passing and failing).</li>
							</ul>
							<p>Watch this space.</p>
						</article>
						<!-- #trends -->
						
						<article id="feedback" class="panel">
							<h3>Feedback</h3>
							<h4>Suggestions? Bugs?</h4>
							<p>Let me know how you&acute;re getting on. You can find me on twitter (<a href="http://twitter.com/scottsweb" title="Scott Evans on twitter">@scottsweb</a>) or ask me a question via my website: <a href="http://scott.ee" title="Scott Evans - Digital Designer">scott.ee</a>. Please supply the URL and score of the site tested when reporting bugs or inaccuracies.</p>
							<!--<a href="http://www.formspring.me/toggle" title="Ask a question on formspring.me" class="replace" id="formspring">Ask a question / submit feedback<span></span></a>-->
						</article>
						<!-- #feedback -->
					</div>					
					<!-- #slider-container -->
				</div>
				<!-- #slider -->	
			</section>
			<!-- #content -->
		</div>
		<!-- #content-container -->
		
		<footer id="footer">
			<nav id="nav">
				<ul>
					<li><a href="#home" class="selected">Home</a></li>
					<li><a href="#help">Help</a></li>
					<li><a href="#trends">Trends</a></li>
					<li><a href="#feedback">Feedback</a></li>
					<li id="flattr"><a class="FlattrButton" style="display:none;" rev="flattr;button:compact;" href="http://pository.scott.ee/"></a></li>
				</ul>
			</nav>
			<!-- #nav -->
			
			<ul id="social">
				<li><a href="http://www.myspace.com/Modules/PostTo/Pages/?u=<?php print urlencode(full_url());?>&amp;t=<?php print urlencode("Pository - The negative news suppository");?>" id="myspace" class="replace social-media" title="Share on MySpace">Share on MySpace<span></span></a></li>
				<li><a href="http://www.facebook.com/share.php?u=<?php print urlencode(full_url());?>&amp;t=<?php print urlencode("Pository - The negative news suppository");?>" id="facebook" class="replace social-media" title="Share on Facebook">Share on Facebook<span></span></a></li>
				<li><a href="http://digg.com/submit?phase=2&amp;url=<?php print urlencode(full_url());?>&amp;title=<?php print urlencode("Pository - The negative news suppository");?>" id="digg" class="replace social-media" title="Share on Digg">Share on Digg<span></span></a></li>
				<li><a href="http://delicious.com/post?url=<?php print urlencode(full_url());?>&amp;title=<?php print urlencode("Pository - The negative news suppository");?>" id="delicious" class="replace social-media" title="Bookmark on Delicious">Bookmark on Delicious<span></span></a></li>
				<li><a href="http://twitter.com/home?status=<?php print urlencode("Pository - The negative news suppository: ".full_url());?>" id="twitter" class="replace social-media" title="">Share on Twitter<span></span></a></li>
				<li><a href="http://www.tumblr.com/share?v=3&amp;u=<?php print urlencode(full_url());?>&amp;t=<?php print urlencode("Pository - The negative news suppository");?>" id="tumblr" class="replace social-media" title="Post to Tumblr">Post to Tumblr<span></span></a></li>
				<li><a href="http://www.stumbleupon.com/submit?url=<?php print urlencode(full_url());?>&amp;title=<?php print urlencode("Pository - The negative news suppository");?>" id="stumbleupon" class="replace social-media" title="StumbleUpon">StumbleUpon<span></span></a></li>
				<li><a href="http://reddit.com/submit?url=<?php print urlencode(full_url());?>&amp;title=<?php print urlencode("Pository - The negative news suppository");?>" id="reddit" class="replace social-media" title="Share on Reddit">Share on Reddit<span></span></a></li>
				<li><a href="http://posterous.com/share?linto=<?php print urlencode(full_url());?>&amp;title=<?php print urlencode("Pository - The negative news suppository");?>" id="posterous" class="replace social-media" title="Post to Posterous">Post to Posterous<span></span></a></li>
			</ul>
			
			<div id="credit">
				<!--<a href="http://www.toggle.uk.com" title="toggle - handmade website design &amp; development" class="replace" id="credit-toggle">Copyright &copy; <?php print date('Y');?> toggle <span></span></a>-->
				<?php
				$c = new Counter();
				if ($c->select(1, 'id')) {
					$count = $c->count;
				} else {
					$count = "Lots of";
				}
				?>
				<p id="credit-stats"><?php print $count;?> suppositories inserted.</p>
			</div>		
		</footer>	
		<!-- #footer -->
		
		<!-- always read the label -->
		<p id="read-label">Always read<br>the label</p>
			
	</div>
	<!-- #container -->	
	
	<!-- link to speclative -->
	<a href="http://speclative.scott.ee/" title="Speclative: The speculative news laxative" class="replace" id="other-banner">Speclative: The speculative news laxative<span></span></a>
	
	<!-- javascript -->
	<script src="/combine.php?type=javascript&amp;files=jquery.js,slider.js,js.js" type="text/javascript"></script>

	<!-- analytics -->
	<script type="text/javascript">
		var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
		document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
	</script>
	<script type="text/javascript">
		try { var pageTracker = _gat._getTracker("UA-3858706-17"); pageTracker._trackPageview(); } catch(err) {}
	</script>
	
</body>
</html>
<?php
	// grab output buffer, compress and print
	$content = ob_get_contents();
	ob_end_clean();
	$search = array('/\>[^\S ]+/s','/[^\S ]+\</s','/(\s)+/s');
	$replace = array('>','<','\\1');
	$content = preg_replace($search, $replace, $content);
	echo $content;

    // page generation time 
    $end = round(microtime(), 4);
	$generation = $end - $start;
	print "<!-- page generated in " . $generation . " seconds -->";
}
?>