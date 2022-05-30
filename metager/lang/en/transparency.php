<?php

return [
    'head.1' => 'Transparency statement',
    'head.2' => 'MetaGer is transparent',
    'head.3' => 'What is a metasearch engine?',
    'head.4' => 'What is the advantage of a metasearch engine?',
    'head.5' => 'How is our ranking made up?',
    'head.compliance' => 'Wie reagiert MetaGer auf Anfragen von Behörden?',


    'text.1' => 'MetaGer is transparent. Our <a href=":sourcecode">source code</a> is <a href=":sourcecode">Quellcode</a>freely licensed</a> and publicly available for all to see. We do not store user data and value data protection and privacy. Therefore we grant anonymous access to the search results. This is possible through an anonymous proxy and TOR-hidden access. In addition, MetaGer has a transparent organizational structure, since it is supported by the non-profit association <a href=":sumalink">SUMA-EV</a> of which anyone can become a member.',
    'text.2.1' => 'To explain what metasearch engines are, it makes sense to first briefly explain roughly how the indexing of regular search engines works. Regular search engines obtain their search results from a database of web pages, which is also called an index. The search engines use so-called "crawlers", which collect web pages and add them to the index (database). The crawler starts with a set of web pages and opens all the web pages linked there. These are indexed, i.e. added to the index. Then the crawler opens the web pages linked on these web pages and continues in this way.',
    'text.2.2' => 'A metasearch engine combines the results of several search engines and evaluates them again according to its own criteria. This means that the metasearch engine does not have its own index. Therefore, metasearch engines do not use crawlers. They use the index of other search engines.',
    'text.3' => 'A clear advantage of metasearch engines is that the user only needs a single search query to access the results of several search engines. The metasearch engine outputs the relevant results in a once again sorted list of results. MetaGer is not a pure metasearch engine, as we also use small indexes of our own.',
    'text.4' => 'We take the rankings from our source search engines and weigh them. These rankings are then converted into scores. Additional points are awarded or deducted for the occurrence of the search terms in the URL and in the snippet, as well as the excessive occurrence of special characters (e.g. other character sets such as Cyrillic). We also use a blocking list to remove individual pages from the results list. We block web pages in the display if we are legally obliged to do so. We also reserve the right to block web pages with demonstrably incorrect information, web pages of extremely poor quality and other particularly dubious web pages.',
    'text.5' => 'If there are any further questions or uncertainties, please feel free to use our <a href=":contact">contact form</a> and ask us your questions!',
    
    'text.compliance' => 'We comply with requests from authorities if we are legally obligated to do so and come to the conclusion that our compliance does not violate fundamental freedoms. We take this review very seriously. In addition, we store as little personal data as possible to reduce the risk of having to release data. In the table below you will find data on the requests from authorities we have processed during the last 5 years. Further information will follow shortly.',


    'table.compliance.th.authinfocomp' => 'Fulfilled requests for information',
    'table.compliance.th.authblockcomp' => 'Fulfilled blocking requests',



];
