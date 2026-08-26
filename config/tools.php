<?php

return [
    /*
    |--------------------------------------------------------------------------
    | System Tools Definition
    |--------------------------------------------------------------------------
    |
    | GenericIntelligence routes use `route` keys `dashboard.marketing.{slug}` and
    | `dashboard.seo.{slug}`. `App\Support\GenericToolRoutes` builds web routes from
    | these entries—edit tools here only, not duplicate slug lists in route files.
    |
    */
    
    'all_tools' => [
        // ---------- THE TOP 4 CORE TOOLS (SEO CATEGORY) ----------
        [
            'name' => 'Keyword Spy Radar',
            'tagline' => 'Spot your next big win before everyone else.',
            'icon' => 'fa-satellite-dish',
            'slug' => 'ai-keyword-radar',
            'color' => '#00f3ff',
            'required_tier' => 'beginner',
            'category' => 'seo',
            'unlock_price' => 99,
            'credit_cost_per_action' => 1,
            'initial_bonus_credits' => 10,
            'description' => 'The industry\'s most powerful keyword surveillance engine. Identify hidden market gaps and outrank competitors with 24/7 autonomous monitoring.',
            'marketing_content' => '<h3 class="marketing-title" style="color: #0ea5e9; font-size: 2rem; font-weight: 800; margin-bottom: 24px; letter-spacing: -0.025em;">Keyword Surveillance Protocol</h3>
                <p class="marketing-subtitle" style="font-size: 1.25rem; line-height: 1.7; margin-bottom: 32px;">
                    Stop guessing. The <strong>Keyword Spy Radar</strong> performs deep-content scans on rival domains to extract the exact high-value keywords they are using to siphon your traffic.
                </p>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 250px), 1fr)); gap: 24px; margin-bottom: 40px;">
                    <div class="marketing-card" style="border-color: rgba(14, 165, 233, 0.2);">
                        <div class="marketing-card-accent" style="background: #0ea5e9;"></div>
                        <h4 class="marketing-card-title">
                            <span style="font-size: 1.5rem; color: #0ea5e9;"><i class="fas fa-user-secret"></i></span> Competitor Gaps
                        </h4>
                        <p class="marketing-card-desc">Extract the exact keywords your rivals rank for that you are missing. Dissect their content footprint to move in first.</p>
                    </div>

                    <div class="marketing-card" style="border-color: rgba(168, 85, 247, 0.2);">
                        <div class="marketing-card-accent" style="background: #a855f7;"></div>
                        <h4 class="marketing-card-title">
                            <span style="font-size: 1.5rem; color: #a855f7;"><i class="fas fa-chart-line"></i></span> Trend Velocity
                        </h4>
                        <p class="marketing-card-desc">Identify rising topics and search momentum shifts in real-time. Catch the wave before it peaks and becomes saturated.</p>
                    </div>

                    <div class="marketing-card" style="border-color: rgba(16, 185, 129, 0.2);">
                        <div class="marketing-card-accent" style="background: #10b981;"></div>
                        <h4 class="marketing-card-title">
                            <span style="font-size: 1.5rem; color: #10b981;"><i class="fas fa-landmark"></i></span> Niche Intelligence
                        </h4>
                        <p class="marketing-card-desc">Automatically discover the underlying keyword structure and sub-topic silos of any market segment or rival domain.</p>
                    </div>

                    <div class="marketing-card" style="border-color: rgba(245, 158, 11, 0.2);">
                        <div class="marketing-card-accent" style="background: #f59e0b;"></div>
                        <h4 class="marketing-card-title">
                            <span style="font-size: 1.5rem; color: #f59e0b;"><i class="fas fa-wave-square"></i></span> Pulse Detection
                        </h4>
                        <p class="marketing-card-desc">Identify 0-day content opportunities across global news feeds before they even hit the search results.</p>
                    </div>
                </div>

                <div class="marketing-features-box">
                    <h3 class="marketing-features-title">Why Choose Keyword Spy Radar?</h3>
                    <ul class="marketing-features-list">
                        <li class="marketing-features-item">
                            <span style="background: #10b98122; color: #10b981; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.9rem;"><i class="fas fa-bolt"></i></span>
                            <div>
                                <strong class="marketing-features-item-title">Extreme Time Savings</strong>
                                <span class="marketing-features-item-desc">Replace 40 hours of manual research with 12 seconds of AI scanning.</span>
                            </div>
                        </li>
                        <li class="marketing-features-item">
                            <span style="background: #0ea5e922; color: #0ea5e9; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.9rem;"><i class="fas fa-bullseye"></i></span>
                            <div>
                                <strong class="marketing-features-item-title">High-ROI Targeting</strong>
                                <span class="marketing-features-item-desc">Target low-competition keywords that are easier to rank and convert better.</span>
                            </div>
                        </li>
                        <li class="marketing-features-item">
                            <span style="background: #a855f722; color: #a855f7; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.9rem;"><i class="fas fa-shield-halved"></i></span>
                            <div>
                                <strong class="marketing-features-item-title">Secure Market Advantage</strong>
                                <span class="marketing-features-item-desc">Stay ahead of algorithm updates by following user intent shifts in real-time.</span>
                            </div>
                        </li>
                    </ul>
                </div>',
            'features' => [
                ['icon' => 'fa-satellite-dish', 'title' => 'Domain Scanning', 'desc' => 'Instant extraction of competitor keyword footprints.'],
                ['icon' => 'fa-bolt', 'title' => 'Emerging Trends', 'desc' => 'Catch high-velocity keywords before they peak.'],
                ['icon' => 'fa-shield-halved', 'title' => 'Gap Detection', 'desc' => '0-day identification of content opportunities.']
            ],
            'meta_title' => 'Keyword Spy Radar | VidaNexus AI',
            'meta_desc' => 'Find high-value topics and hidden market gaps before your competitors.',
            'route' => 'dashboard.ai-keyword-radar.index',
            'daily_limit' => 2,
        ],
        [
            'name' => 'Discover Headlines',
            'tagline' => 'Magnetic titles that stop the scroll.',
            'icon' => 'fa-magic',
            'slug' => 'discover-headlines',
            'color' => '#ffcc00',
            'required_tier' => 'beginner',
            'category' => 'seo',
            'unlock_price' => 99,
            'credit_cost_per_action' => 1,
            'initial_bonus_credits' => 10,
            'description' => 'Get more clicks without the guesswork. Create stunning headlines that capture your audience’s curiosity and ensure your content gets the attention it deserves.',
            'marketing_content' => '<h3 style="color: #f59e0b; font-size: 2rem; font-weight: 800; margin-bottom: 24px; letter-spacing: -0.025em;">Semantic Headline Intelligence</h3>
                <p style="font-size: 1.25rem; line-height: 1.7; color: rgba(255,255,255,0.9); margin-bottom: 32px;">
                    Stop fighting the algorithm and start mastering it. <strong>Discover Headlines</strong> is a semantic powerhouse that fuses high-level psychology with Google’s Knowledge Graph to ensure your content dominates Google Discover, News, and Search.
                </p>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 250px), 1fr)); gap: 24px; margin-bottom: 40px;">
                    <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(245, 158, 11, 0.2); padding: 28px; border-radius: 16px; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: #f59e0b;"></div>
                        <h4 style="color: #fff; font-size: 1.3rem; margin-bottom: 12px; display: flex; align-items: center; gap: 10px; font-weight: 900;">
                            <span style="font-size: 1.5rem; color: #f59e0b;"><i class="fas fa-fingerprint"></i></span> Entity Authority
                        </h4>
                        <p style="color: rgba(255,255,255,0.7); line-height: 1.6; margin: 0; font-style: italic; font-size: 0.9rem;">Automatically inject high-value entities and LSI keywords that signal Expertise (E-E-A-T) to search engines instantly.</p>
                    </div>

                    <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(16, 185, 129, 0.2); padding: 28px; border-radius: 16px; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: #10b981;"></div>
                        <h4 style="color: #fff; font-size: 1.3rem; margin-bottom: 12px; display: flex; align-items: center; gap: 10px; font-weight: 900;">
                            <span style="font-size: 1.5rem; color: #10b981;"><i class="fas fa-bolt-lightning"></i></span> Sentiment Sync
                        </h4>
                        <p style="color: rgba(255,255,255,0.7); line-height: 1.6; margin: 0; font-style: italic; font-size: 0.9rem;">Analyze audience emotional cues to craft triggers that resonate, shock, or satisfy—driving record-breaking click-through rates.</p>
                    </div>

                    <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(14, 165, 233, 0.2); padding: 28px; border-radius: 16px; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: #0ea5e9;"></div>
                        <h4 style="color: #fff; font-size: 1.3rem; margin-bottom: 12px; display: flex; align-items: center; gap: 10px; font-weight: 900;">
                            <span style="font-size: 1.5rem; color: #0ea5e9;"><i class="fas fa-image"></i></span> Visual Synergy
                        </h4>
                        <p style="color: rgba(255,255,255,0.7); line-height: 1.6; margin: 0; font-style: italic; font-size: 0.9rem;">Beyond text, we suggest the exact "Visual Angle" for your thumbnails, creating a unified, high-performance click-magnet.</p>
                    </div>

                    <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(168, 85, 247, 0.2); padding: 28px; border-radius: 16px; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: #a855f7;"></div>
                        <h4 style="color: #fff; font-size: 1.3rem; margin-bottom: 12px; display: flex; align-items: center; gap: 10px; font-weight: 900;">
                            <span style="font-size: 1.5rem; color: #a855f7;"><i class="fas fa-brain"></i></span> Curiosity Engine
                        </h4>
                        <p style="color: rgba(255,255,255,0.7); line-height: 1.6; margin: 0; font-style: italic; font-size: 0.9rem;">Analyze psychological triggers and search intent to craft headlines that naturally attract clicks without clickbait.</p>
                    </div>
                </div>',
            'features' => [
                ['icon' => 'fa-fingerprint', 'title' => 'Semantic Entity Mapping', 'desc' => 'Sync your titles with Google\'s Knowledge Graph for maximum topical authority.'],
                ['icon' => 'fa-bolt-lightning', 'title' => 'Emotional Pulse Sync', 'desc' => 'Leverage psychological triggers like Surprise and Urgency to stop the scroll.'],
                ['icon' => 'fa-brain', 'title' => 'Curiosity Optimization', 'desc' => 'Fine-tune headlines for maximum click-through rates based on AI psychological modeling.']
            ],
            'meta_title' => 'Discover Headlines | VidaNexus AI',
            'meta_desc' => 'Generate high-engagement headlines in Arabic and English to maximize your reach.',
            'route' => 'headlines.index',
            'daily_limit' => 2,

        ],
        [
            'name' => 'News Intelligence',
            'tagline' => 'Spot the next viral wave with real-time global news surveillance.',
            'icon' => 'fa-satellite-dish',
            'slug' => 'global-news-monitor',
            'color' => '#0ea5e9',
            'required_tier' => 'beginner',
            'category' => 'seo',
            'unlock_price' => 99,
            'credit_cost_per_action' => 1,
            'initial_bonus_credits' => 10,
            'description' => 'A high-performance radar system for 24/7 monitoring of global agencies, featuring intelligent SEO scoring and deep-angle discovery before trends go viral.',
            'marketing_content' => '<h3 style="color: #0ea5e9; font-size: 2.2rem; font-weight: 900; margin-bottom: 24px; letter-spacing: -0.025em;">News Intelligence Protocol</h3>
                <p style="font-size: 1.25rem; line-height: 1.8; color: rgba(255,255,255,0.9); margin-bottom: 32px;">
                    In the high-stakes world of content publishing, speed isn’t just an advantage—it’s the only currency that matters. With <strong>News Intelligence</strong>, you stop following the news and start outrunning it. Our AI engine scans thousands of global sources in seconds to reveal hidden opportunities.
                </p>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 250px), 1fr)); gap: 24px; margin-bottom: 40px;">
                    <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(14, 165, 233, 0.2); padding: 28px; border-radius: 20px; position: relative; overflow: hidden; backdrop-filter: blur(10px);">
                        <div style="position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: #0ea5e9;"></div>
                        <h4 style="color: #fff; font-size: 1.4rem; margin-bottom: 12px; display: flex; align-items: center; gap: 12px; font-weight: 800;">
                            <span style="font-size: 1.6rem; color: #0ea5e9;"><i class="fas fa-globe-americas"></i></span> Global Surveillance
                        </h4>
                        <p style="color: rgba(255,255,255,0.7); line-height: 1.7; margin: 0; font-size: 0.95rem;">Monitor the US, UK, Middle East, and Europe simultaneously. Replace hours of manual scanning with a single, unified intelligence feed.</p>
                    </div>

                    <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(16, 185, 129, 0.2); padding: 28px; border-radius: 20px; position: relative; overflow: hidden; backdrop-filter: blur(10px);">
                        <div style="position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: #10b981;"></div>
                        <h4 style="color: #fff; font-size: 1.4rem; margin-bottom: 12px; display: flex; align-items: center; gap: 12px; font-weight: 800;">
                            <span style="font-size: 1.6rem; color: #10b981;"><i class="fas fa-rocket"></i></span> SEO Opportunity Score
                        </h4>
                        <p style="color: rgba(255,255,255,0.7); line-height: 1.7; margin: 0; font-size: 0.95rem;">Our logic calculates the "Viral Potential" of every news item, showing you exactly which stories will dominate Google Discover and Search.</p>
                    </div>

                    <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(245, 158, 11, 0.2); padding: 28px; border-radius: 20px; position: relative; overflow: hidden; backdrop-filter: blur(10px);">
                        <div style="position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: #f59e0b;"></div>
                        <h4 style="color: #fff; font-size: 1.4rem; margin-bottom: 12px; display: flex; align-items: center; gap: 12px; font-weight: 800;">
                            <span style="font-size: 1.6rem; color: #f59e0b;"><i class="fas fa-brain"></i></span> Deep-Angle Analytics
                        </h4>
                        <p style="color: rgba(255,255,255,0.7); line-height: 1.7; margin: 0; font-size: 0.95rem;">Leverage AI to discover unique narrative angles, LSI keywords, and audience sentiment shifts specifically tailored for each major headline.</p>
                    </div>

                    <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(168, 85, 247, 0.2); padding: 28px; border-radius: 20px; position: relative; overflow: hidden; backdrop-filter: blur(10px);">
                        <div style="position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: #a855f7;"></div>
                        <h4 style="color: #fff; font-size: 1.4rem; margin-bottom: 12px; display: flex; align-items: center; gap: 12px; font-weight: 800;">
                            <span style="font-size: 1.6rem; color: #a855f7;"><i class="fas fa-bolt"></i></span> Zero-Day Trend Spotting
                        </h4>
                        <p style="color: rgba(255,255,255,0.7); line-height: 1.7; margin: 0; font-size: 0.95rem;">Identify explosive stories long before they hit the local mainstream. Secure your ranking while the competition is still sleeping.</p>
                    </div>
                </div>

                <div style="background: linear-gradient(135deg, rgba(14, 165, 233, 0.1), rgba(16, 185, 129, 0.05)); border-radius: 24px; padding: 40px; text-align: center; border: 1px solid rgba(255,255,255,0.1); position: relative; overflow: hidden;">
                    <h3 style="color: #fff; margin-bottom: 30px; font-size: 1.8rem; font-weight: 900;">Why Publishers Choose News Intelligence?</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                        <div style="background: rgba(255,255,255,0.03); padding: 20px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.05);">
                            <div style="color: #0ea5e9; font-size: 1.5rem; margin-bottom: 10px;"><i class="fas fa-money-bill-trend-up"></i></div>
                            <strong style="color: #fff; display: block; margin-bottom: 5px;">Skyrocket Discover Traffic</strong>
                            <span style="font-size: 0.9rem; color: rgba(255,255,255,0.6);">Targeting high-score news guarantees wider visibility in Google Discover feeds.</span>
                        </div>
                        <div style="background: rgba(255,255,255,0.03); padding: 20px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.05);">
                            <div style="color: #10b981; font-size: 1.5rem; margin-bottom: 10px;"><i class="fas fa-hourglass-start"></i></div>
                            <strong style="color: #fff; display: block; margin-bottom: 5px;">Save 90% Research Time</strong>
                            <span style="font-size: 0.9rem; color: rgba(255,255,255,0.6);">Automate your manual news-gathering and focus entirely on content execution.</span>
                        </div>
                        <div style="background: rgba(255,255,255,0.03); padding: 20px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.05);">
                            <div style="color: #a855f7; font-size: 1.5rem; margin-bottom: 10px;"><i class="fas fa-award"></i></div>
                            <strong style="color: #fff; display: block; margin-bottom: 5px;">Establish E-E-A-T Authority</strong>
                            <span style="font-size: 0.9rem; color: rgba(255,255,255,0.6);">Publishing informed, unique angles builds massive credibility with algorithms.</span>
                        </div>
                        <div style="background: rgba(255,255,255,0.03); padding: 20px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.05);">
                            <div style="color: #f59e0b; font-size: 1.5rem; margin-bottom: 10px;"><i class="fas fa-coins"></i></div>
                            <strong style="color: #fff; display: block; margin-bottom: 5px;">Monetization Velocity</strong>
                            <span style="font-size: 0.9rem; color: rgba(255,255,255,0.6);">Faster reporting on viral trends translates directly into peak CPM and maximized ad revenue.</span>
                        </div>
                    </div>
                </div>',
            'features' => [
                ['icon' => 'fa-satellite', 'title' => '24/7 Global Feed', 'desc' => 'Real-time tracking of international and local news agencies.'],
                ['icon' => 'fa-bullseye', 'title' => 'SEO Score Analysis', 'desc' => 'Identify high-potential news items for maximum search visibility.'],
                ['icon' => 'fa-wand-magic-sparkles', 'title' => 'Deep Angle Discovery', 'desc' => 'Find unique narrative perspectives to stand out from the noise.'],
                ['icon' => 'fa-shield-halved', 'title' => 'Anti-Clutter Filter', 'desc' => 'Smart AI filtering to remove duplicate stories and focus on unique value.'],
                ['icon' => 'fa-language', 'title' => 'Multi-Region Sync', 'desc' => 'Seamlessly track and correlate news stories across international borders.'],
                ['icon' => 'fa-chart-pie', 'title' => 'Predictive Analytics', 'desc' => 'Our proprietary logic identifies emerging trends before they peak.']
            ],
            'meta_title' => 'Global News Intelligence & Trend Monitoring | VidaNexus AI',
            'meta_desc' => 'Catch viral trends with real-time news intelligence. Analyze SEO scores, discover unique angles, and dominate global search markets.',
            'route' => 'dashboard.global-news-monitor.index',
            'daily_limit' => 10,
        ],
        [
            'name' => 'Viral Search Monitor',
            'tagline' => 'Ride the wave of what people want right now.',
            'icon' => 'fa-chart-line',
            'slug' => 'trending-search-monitor',
            'color' => '#ff3e00',
            'required_tier' => 'beginner',
            'category' => 'seo',
            'unlock_price' => 99,
            'credit_cost_per_action' => 1,
            'initial_bonus_credits' => 10,
            'description' => 'See what millions are searching for in real-time. Identify explosive trends the moment they start.',
            'marketing_content' => '<h3>The Search Logic of the Masses</h3><p>Catching a trend at the start is worth 100x more than catching it at the peak. <b>Viral Search Monitor</b> tracks trillions of search queries to reveal what the world is curious about right now.</p><ul><li><b>Explosive Growth Alerts:</b> Notifications for search terms that have spiked over 500% in the last hour.</li><li><b>Intent Analysis:</b> Understand WHY people are searching, not just what they are typing.</li><li><b>Competitive Front-Running:</b> Be the first to publish on a topic before your competitors even see it in their standard tools.</li></ul>',
            'features' => [
                ['icon' => 'fa-fire', 'title' => 'Trend Spotting', 'desc' => 'Find the search terms that are exploding in popularity right now.'],
                ['icon' => 'fa-clock-rotate-left', 'title' => 'Predictive Insights', 'desc' => 'Identify which trends are here to stay and which are just passing spikes.'],
                ['icon' => 'fa-link', 'title' => 'One-Click Action', 'desc' => 'Move instantly from finding a trend to planning your next viral content piece.']
            ],
            'meta_title' => 'Viral Search Monitor | VidaNexus AI',
            'meta_desc' => 'Track viral queries and emerging search trends in real-time.',
            'route' => 'dashboard.trending-searches.index',
            'daily_limit' => 10,
        ],
        [
            'name' => 'Pro AI Article Writer',
            'tagline' => 'Generate fully optimized articles in seconds.',
            'icon' => 'fa-file-lines',
            'slug' => 'article-writer',
            'color' => '#14b8a6',
            'required_tier' => 'pro',
            'category' => 'content',
            'unlock_price' => 499,
            'credit_cost_per_action' => 5,
            'initial_bonus_credits' => 10,
            'description' => 'A high-performance AI content laboratory designed for professional editors. Generate factual, research-grounded, and SEO-optimized long-form articles with real-time news integration and custom persona control.',
            'marketing_content' => '<h3 style="color: #14b8a6; font-size: 2.2rem; font-weight: 900; margin-bottom: 24px; letter-spacing: -0.025em;">The Content Engineering Laboratory</h3>
                <p style="font-size: 1.25rem; line-height: 1.8; color: rgba(255,255,255,0.9); margin-bottom: 32px;">
                    Stop publishing static, hallucination-prone AI text. The <strong>Pro AI Article Writer</strong> is a state-of-the-art content engine that fuses cutting-edge LLMs with <strong>Live Research Grounding</strong>. Every article is built on real-time facts, tailored to your specific audience, and optimized for 2026 search algorithms.
                </p>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 250px), 1fr)); gap: 24px; margin-bottom: 40px;">
                    <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(20, 184, 166, 0.2); padding: 28px; border-radius: 20px; position: relative; overflow: hidden; backdrop-filter: blur(10px);">
                        <div style="position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: #14b8a6;"></div>
                        <h4 style="color: #fff; font-size: 1.4rem; margin-bottom: 12px; display: flex; align-items: center; gap: 12px; font-weight: 800;">
                            <span style="font-size: 1.6rem; color: #14b8a6;"><i class="fas fa-microchip"></i></span> Live Research Engine
                        </h4>
                        <p style="color: rgba(255,255,255,0.7); line-height: 1.7; margin: 0; font-size: 0.95rem;">Our system performs real-time Google News scans for your keyword, injecting the latest facts and data into the AI’s context window to eliminate hallucinations.</p>
                    </div>

                    <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(168, 85, 247, 0.2); padding: 28px; border-radius: 20px; position: relative; overflow: hidden; backdrop-filter: blur(10px);">
                        <div style="position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: #a855f7;"></div>
                        <h4 style="color: #fff; font-size: 1.4rem; margin-bottom: 12px; display: flex; align-items: center; gap: 12px; font-weight: 800;">
                            <span style="font-size: 1.6rem; color: #a855f7;"><i class="fas fa-mask"></i></span> Persona Factory
                        </h4>
                        <p style="color: rgba(255,255,255,0.7); line-height: 1.7; margin: 0; font-size: 0.95rem;">Switch between 10+ professional editorial tones and target audiences. From "Deep Technical" to "Viral Storytelling"—your content always sounds human.</p>
                    </div>

                    <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(16, 185, 129, 0.2); padding: 28px; border-radius: 20px; position: relative; overflow: hidden; backdrop-filter: blur(10px);">
                        <div style="position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: #10b981;"></div>
                        <h4 style="color: #fff; font-size: 1.4rem; margin-bottom: 12px; display: flex; align-items: center; gap: 12px; font-weight: 800;">
                            <span style="font-size: 1.6rem; color: #10b981;"><i class="fas fa-sitemap"></i></span> Semantic Architecture
                        </h4>
                        <p style="color: rgba(255,255,255,0.7); line-height: 1.7; margin: 0; font-size: 0.95rem;">Strict H1-H3 hierarchy, LSI keyword distribution, snippet-ready FAQs, and key takeaway boxes—all built-in for maximum SEO dominance.</p>
                    </div>

                    <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(245, 158, 11, 0.2); padding: 28px; border-radius: 20px; position: relative; overflow: hidden; backdrop-filter: blur(10px);">
                        <div style="position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: #f59e0b;"></div>
                        <h4 style="color: #fff; font-size: 1.4rem; margin-bottom: 12px; display: flex; align-items: center; gap: 12px; font-weight: 800;">
                            <span style="font-size: 1.6rem; color: #f59e0b;"><i class="fas fa-maximize"></i></span> Scalable Production
                        </h4>
                        <p style="color: rgba(255,255,255,0.7); line-height: 1.7; margin: 0; font-size: 0.95rem;">Choose your exact scale: from rapid 300-word news briefs to comprehensive 2000+ word ultimate guides, all with a single click.</p>
                    </div>
                </div>

                <div style="background: linear-gradient(135deg, rgba(20, 184, 166, 0.1), rgba(168, 85, 247, 0.05)); border-radius: 24px; padding: 40px; text-align: center; border: 1px solid rgba(255,255,255,0.1); position: relative; overflow: hidden;">
                    <h3 style="color: #fff; margin-bottom: 30px; font-size: 1.8rem; font-weight: 900;">Why Content Teams Trust Pro AI?</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
                        <div style="background: rgba(255,255,255,0.03); padding: 20px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.05);">
                            <div style="color: #14b8a6; font-size: 1.5rem; margin-bottom: 10px;"><i class="fas fa-check-double"></i></div>
                            <strong style="color: #fff; display: block; margin-bottom: 5px;">Zero Hallucinations</strong>
                            <span style="font-size: 0.9rem; color: rgba(255,255,255,0.6);">Fact-checked against live news results.</span>
                        </div>
                        <div style="background: rgba(255,255,255,0.03); padding: 20px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.05);">
                            <div style="color: #a855f7; font-size: 1.5rem; margin-bottom: 10px;"><i class="fas fa-language"></i></div>
                            <strong style="color: #fff; display: block; margin-bottom: 5px;">Native Fluency</strong>
                            <span style="font-size: 0.9rem; color: rgba(255,255,255,0.6);">Support for 20+ languages natively.</span>
                        </div>
                        <div style="background: rgba(255,255,255,0.03); padding: 20px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.05);">
                            <div style="color: #10b981; font-size: 1.5rem; margin-bottom: 10px;"><i class="fas fa-bolt"></i></div>
                            <strong style="color: #fff; display: block; margin-bottom: 5px;">90% Faster</strong>
                            <span style="font-size: 0.9rem; color: rgba(255,255,255,0.6);">Research to Ready-to-Publish in seconds.</span>
                        </div>
                        <div style="background: rgba(255,255,255,0.03); padding: 20px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.05);">
                            <div style="color: #f59e0b; font-size: 1.5rem; margin-bottom: 10px;"><i class="fas fa-award"></i></div>
                            <strong style="color: #fff; display: block; margin-bottom: 5px;">E-E-A-T Compliant</strong>
                            <span style="font-size: 0.9rem; color: rgba(255,255,255,0.6);">Built for Google Authority standards.</span>
                        </div>
                        <div style="background: rgba(255,255,255,0.03); padding: 20px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.05);">
                            <div style="color: #0ea5e9; font-size: 1.5rem; margin-bottom: 10px;"><i class="fas fa-image"></i></div>
                            <strong style="color: #fff; display: block; margin-bottom: 5px;">Visual Meta Guide</strong>
                            <span style="font-size: 0.9rem; color: rgba(255,255,255,0.6);">Suggested thumbnail and visual angles.</span>
                        </div>
                        <div style="background: rgba(255,255,255,0.03); padding: 20px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.05);">
                            <div style="color: #6366f1; font-size: 1.5rem; margin-bottom: 10px;"><i class="fas fa-cloud"></i></div>
                            <strong style="color: #fff; display: block; margin-bottom: 5px;">Smart History Cloud</strong>
                            <span style="font-size: 0.9rem; color: rgba(255,255,255,0.6);">Cloud-archived articles for instant retrieval.</span>
                        </div>
                    </div>
                </div>',
            'features' => [
                ['icon' => 'fa-microchip', 'title' => 'Live Grounding', 'desc' => 'Articles are grounded in real-time facts from global news networks.'],
                ['icon' => 'fa-mask', 'title' => 'Tone Mastery', 'desc' => 'Switch between professional, conversational, or viral personalities.'],
                ['icon' => 'fa-list-ol', 'title' => 'SEO Hierarchy', 'desc' => 'Automated H1-H3 tagging and LSI keyword optimization.'],
                ['icon' => 'fa-language', 'title' => 'Global Mastery', 'desc' => 'Generate flawless content across dozens of languages natively.'],
                ['icon' => 'fa-chart-pie', 'title' => 'Structural Tools', 'desc' => 'Integrated summary boxes, takeaways, and FAQ schema.'],
                ['icon' => 'fa-maximize', 'title' => 'Scalable Length', 'desc' => 'Full control over article depth from short-form to epic guides.']
            ],
            'meta_title' => 'Pro AI Article Writer | Enterprise Content Generation',
            'meta_desc' => 'Generate research-grounded, SEO-optimized long-form articles with real-time news integration and professional persona control.',
            'route' => 'dashboard.article-writer.index',
            'daily_limit' => 5,
        ],

        // ---------- THE REST OF THE TOOLS ----------

        [
            'name' => 'Competitor X-Ray Engine',
            'tagline' => 'Dominate your niche by reverse-engineering competitor strategies.',
            'icon' => 'fa-crosshairs',
            'slug' => 'competitor-xray',
            'color' => '#ff0055',
            'required_tier' => 'pro',
            'category' => 'intelligence',
            'unlock_price' => 499,
            'credit_cost_per_action' => 3,
            'initial_bonus_credits' => 10,
            'description' => 'Input any competitor domain and instantly uncover their top keywords, find your content gaps, and generate superior articles to outrank them with a single click.',
            'marketing_content' => '<h3>See Through the Competition</h3><p>Why start from scratch when you can see exactly what is already working? The <b>Competitor X-Ray Engine</b> gives you a surgical view into your rivals\' traffic sources. It doesn\'t just list keywords; it analyzes their content structure, backlink patterns, and ranking stability.</p><ul><li><b>Keyword Theft:</b> Identify the high-performing keywords your competitors are "protecting" and take them for yourself.</li><li><b>Content Gap Matrix:</b> See a side-by-side comparison of your content vs. the market leaders.</li><li><b>Instant Outranking:</b> Generate content outlines that are mathematically superior to the current top result.</li></ul>',
            'features' => [
                ['icon' => 'fa-magnifying-glass-chart', 'title' => 'Deep Domain Analysis', 'desc' => 'Instantly discover the exact keywords driving organic traffic to any competitor.'],
                ['icon' => 'fa-chart-pie', 'title' => 'Content Gap Matrix', 'desc' => 'Find the high-value topics your competitors are ranking for but you are missing.'],
                ['icon' => 'fa-bolt', 'title' => 'One-Click Domination', 'desc' => 'Send gap opportunities directly to the Article Writer to generate better content instantly.']
            ],
            'meta_title' => 'Competitor X-Ray Engine | VidaNexus AI',
            'meta_desc' => 'Analyze competitors and find content gaps to dominate your niche.',
            'route' => 'dashboard.competitor-xray.index',
            'daily_limit' => 5,
        ],
        [
            'name' => 'Traffic Velocity Auditor',
            'tagline' => 'Clear insights for your digital success.',
            'icon' => 'fa-magnifying-glass-chart',
            'slug' => 'seo-analyzer',
            'color' => '#10b981',
            'required_tier' => 'growth',
            'category' => 'seo',
            'unlock_price' => 299,
            'credit_cost_per_action' => 2,
            'initial_bonus_credits' => 10,
            'description' => 'Take the mystery out of your traffic. Get clear, easy-to-understand reports on how your website is performing.',
            'marketing_content' => '<h3>Data Without the Headache</h3><p>Stop drowning in complex spreadsheets. The <b>Traffic Velocity Auditor</b> turns raw data into a visual story of your growth path.</p><ul><li><b>Conversion Bottleneck Finder:</b> Identify exactly where users are dropping off and why.</li><li><b>Automated Board Presentations:</b> Turn your monthly results into stunning slides with zero manual effort.</li><li><b>Author Performance Index:</b> See which members of your content team are driving the highest ROI.</li></ul>',
            'features' => [
                ['icon' => 'fa-google', 'title' => 'Clear Performance Reports', 'desc' => 'See exactly how many people are visiting and what they are clicking on without the technical headache.'],
                ['icon' => 'fa-mobile-screen', 'title' => 'Team Performance Tracking', 'desc' => 'Understand which authors or sections are driving the most results for your brand.'],
                ['icon' => 'fa-bolt', 'title' => 'Live Traffic Monitoring', 'desc' => 'Watch your audience grow in real-time and see which pages are popular at this very moment.'],
                ['icon' => 'fa-file-powerpoint', 'title' => 'Automatic Presentations', 'desc' => 'Turn your data into professional slides for meetings or clients with a single click.']
            ],
            'meta_title' => 'Traffic Velocity Auditor | VidaNexus AI',
            'meta_desc' => 'Clear website performance reports and automated presentation generation.',
            'route' => 'dashboard.seo-analyzer.index',
            'daily_limit' => 2,
        ],
        [
            'name' => 'EEAT Content Strategy Lab',
            'tagline' => 'Analyze content for SEO and NLP optimization.',
            'icon' => 'fa-brain',
            'slug' => 'nlp-entities-analysis',
            'color' => '#a855f7',
            'required_tier' => 'pro',
            'category' => 'intelligence',
            'unlock_price' => 499,
            'credit_cost_per_action' => 3,
            'initial_bonus_credits' => 10,
            'description' => 'Analyze your content to understand search intent, extract entities, and identify missing topics needed to strengthen your SEO visibility.',
            'marketing_content' => '<h3>The Future of Content Authority</h3><p>Google no longer just reads keywords; it understands <b>Entities</b>. The <b>EEAT Content Strategy Lab</b> uses professional NLP (Natural Language Processing) to ensure your content is seen as "Highly Authoritative".</p><ul><li><b>Topical Completeness:</b> Discover the "missing pieces" that competitors are using to outrank you on specific subjects.</li><li><b>E-E-A-T Scoring:</b> Get a real-time score on your Experience, Expertise, Authoritativeness, and Trustworthiness.</li><li><b>Search Intent Calibration:</b> Verify if your content is answering the Informational, Navigational, or Transactional intent of the user.</li></ul>',
            'features' => [
                ['icon' => 'fa-dna', 'title' => 'NLP & Entity Extraction', 'desc' => 'Identify the key entities (people, places, brands) Google uses to understand your content.'],
                ['icon' => 'fa-magnifying-glass-plus', 'title' => 'Search Intent Alignment', 'desc' => 'Ensure your content perfectly matches what users are actually looking for.'],
                ['icon' => 'fa-robot', 'title' => 'AI Content Advisor', 'desc' => 'Get real-time suggestions on how to improve your E-E-A-T and topical authority.']
            ],
            'meta_title' => 'EEAT Content Strategy Lab | VidaNexus AI',
            'meta_desc' => 'Professional NLP content analysis and entity extraction for SEO.',
            'route' => 'dashboard.nlp-entities.index',
            'daily_limit' => 5,
        ],
        [
            'name' => 'AIO Optimizer',
            'tagline' => 'Maximize visibility in Google AI Overviews.',
            'icon' => 'fa-robot',
            'slug' => 'aio-optimizer',
            'color' => '#8b5cf6',
            'required_tier' => 'pro',
            'category' => 'seo',
            'unlock_price' => 499,
            'credit_cost_per_action' => 3,
            'initial_bonus_credits' => 10,
            'description' => 'Analyze your URL to predict AI Overview visibility, identify semantic gaps, and generate high-authority content rewrites.',
            'marketing_content' => '<h3>The Master Blueprint for AIO Dominance</h3><p>The search landscape is changing. Google AI Overviews (AIO) are the new #1. The <b>AIO Optimizer</b> is your technical edge to ensure your content is cited, not ignored.</p><ul><li><b>Citation Probability Scoring:</b> Know exactly how likely you are to be featured as a source in the AI answer.</li><li><b>The Missing Link Analysis:</b> Identify critical entities present in the AIO target but missing from your page.</li><li><b>Snippet Architect:</b> Real-time "AI-Ready" paragraph refactoring based on Google\'s citation logic.</li></ul>',
            'features' => [
                ['icon' => 'fa-gauge-high', 'title' => 'Visibility Meter', 'desc' => 'Calculate your citation probability with deep factualness and structural analysis.'],
                ['icon' => 'fa-link-slash', 'title' => 'The Missing Link', 'desc' => 'Map the semantic gap between your content and the ideal AI summary.'],
                ['icon' => 'fa-wand-magic-sparkles', 'title' => 'Snippet Architect', 'desc' => 'Generate high-authority content refactors that Google AIO favors for citations.']
            ],
            'meta_title' => 'AIO Optimizer | VidaNexus AI',
            'meta_desc' => 'Optimize content for Google AI Overviews (AIO) using deep semantic analysis.',
            'route' => 'dashboard.aio-optimizer.index',
            'daily_limit' => 5,
        ],
        [
            'name' => 'Conversion Copy Master',
            'tagline' => 'Generate high-converting ad copy instantly.',
            'icon' => 'fa-ad',
            'slug' => 'ad-copy-generator',
            'color' => '#fbbf24',
            'required_tier' => 'beginner',
            'category' => 'marketing',
            'unlock_price' => 99,
            'credit_cost_per_action' => 1,
            'initial_bonus_credits' => 10,
            'description' => 'Create compelling ad copy for Google, Facebook, Instagram, and LinkedIn that drives clicks and conversions.',
            'marketing_content' => '<h3>Turn Words Into Wealth</h3><p>Stop staring at a blank page. The <b>Conversion Copy Master</b> uses proven psychological frameworks to write ads that people actually click.</p><ul><li><b>Framework Integration:</b> Choose between AIDA (Attention, Interest, Desire, Action) or PAS (Problem, Agitation, Solution) for maximum impact.</li><li><b>Cross-Channel Nuance:</b> Automatically adapts the tone from professional (LinkedIn) to visual/emotive (Instagram).</li><li><b>High-Scale A/B Testing:</b> Generate 20+ variants in seconds to find your winning message with minimal spend.</li></ul>',
            'features' => [
                ['icon' => 'fa-rectangle-ad', 'title' => 'Multi-Platform Support', 'desc' => 'Tailored copy for Google Search, FB/IG feeds, and LinkedIn professional ads.'],
                ['icon' => 'fa-bolt', 'title' => 'High-Conversion Frameworks', 'desc' => 'Uses AIDA and PAS frameworks to ensure your message hits home.'],
                ['icon' => 'fa-wand-magic-sparkles', 'title' => 'Instant Variations', 'desc' => 'Generate dozens of variations to A/B test your best performing copy.']
            ],
            'meta_title' => 'Conversion Copy Master | VidaNexus AI',
            'meta_desc' => 'Generate high-converting ad copy for all social and search platforms.',
            'route' => 'dashboard.marketing.ad-copy',
            'daily_limit' => 10,
        ],
        [
            'name' => 'Engagement Pulse Architect',
            'tagline' => 'Create engaging social media posts.',
            'icon' => 'fa-share-nodes',
            'slug' => 'social-post-generator',
            'color' => '#3b82f6',
            'required_tier' => 'beginner',
            'category' => 'marketing',
            'unlock_price' => 99,
            'credit_cost_per_action' => 1,
            'initial_bonus_credits' => 10,
            'description' => 'Turn any idea or link into engaging posts for Twitter, LinkedIn, and Facebook.',
            'marketing_content' => '<h3>The End of Boring Social</h3><p>Social media is a conversation, not a broadcast. The <b>Engagement Pulse Architect</b> crafts posts that spark dialogue and drive shares.</p><ul><li><b>The Hook Library:</b> Access hundreds of proven "Scroll-Stopper" openings.</li><li><b>Content Repurposing:</b> Paste a blog URL and get a 10-post social thread in seconds.</li><li><b>Narrative Pacing:</b> Scripts your posts for maximum retention and interaction.</li></ul>',
            'features' => [
                ['icon' => 'fa-hashtag', 'title' => 'Viral Hook Library', 'desc' => 'Access a library of proven hooks to stop the scroll.'],
                ['icon' => 'fa-clock', 'title' => 'Platform Optimization', 'desc' => 'Automatically formats posts for the specific length and style of each network.'],
                ['icon' => 'fa-face-smile', 'title' => 'Tone Controller', 'desc' => 'Switch between professional, witty, or educational tones with one click.']
            ],
            'meta_title' => 'Engagement Pulse Architect | VidaNexus AI',
            'meta_desc' => 'Create viral social media posts using advanced AI engagement logic.',
            'route' => 'dashboard.marketing.social-posts',
            'daily_limit' => 10,
        ],
        [
            'name' => 'SERP Domination Suite',
            'tagline' => 'Perfect titles and descriptions every time.',
            'icon' => 'fa-magnifying-glass',
            'slug' => 'seo-meta-generator',
            'color' => '#10b981',
            'required_tier' => 'beginner',
            'category' => 'seo',
            'unlock_price' => 99,
            'credit_cost_per_action' => 1,
            'initial_bonus_credits' => 10,
            'description' => 'Generate optimized meta titles and descriptions that boost CTR.',
            'marketing_content' => '<h3>Win the Search Result Battle</h3><p>Your ranking matters, but the CLICK is what counts. The <b>SERP Domination Suite</b> creates meta-data that captures the eye and satisfies the algorithm.</p><ul><li><b>CTR Optimization:</b> Uses power words and emotional triggers to ensure your link is the one that gets clicked.</li><li><b>Algorithm Compliance:</b> Automatically prevents truncation by monitoring pixel-width and character counts.</li><li><b>Bulk Engine:</b> Optimize your entire catalog or blog archive in one sitting.</li></ul>',
            'features' => [
                ['icon' => 'fa-tag', 'title' => 'Bulk Generation', 'desc' => 'Create meta tags for hundreds of pages in seconds.'],
                ['icon' => 'fa-arrows-left-right', 'title' => 'Pixel-Perfect Length', 'desc' => 'Ensures your titles and descriptions are never truncated in SERPs.'],
                ['icon' => 'fa-keyword', 'title' => 'Keyword Integration', 'desc' => 'Intelligent placement of your primary and secondary keywords for max impact.']
            ],
            'meta_title' => 'SERP Domination Suite | VidaNexus AI',
            'meta_desc' => 'Pixel-perfect SEO meta titles and descriptions that drive clicks.',
            'route' => 'dashboard.seo.meta-generator',
            'daily_limit' => 20,
        ],
        [
            'name' => 'Authority Q&A Engine',
            'tagline' => 'Instant FAQs for your content.',
            'icon' => 'fa-circle-question',
            'slug' => 'faq-generator',
            'color' => '#f59e0b',
            'required_tier' => 'beginner',
            'category' => 'content',
            'unlock_price' => 99,
            'credit_cost_per_action' => 1,
            'initial_bonus_credits' => 10,
            'description' => 'Generate relevant FAQs and schema markup based on your content or keywords.',
            'marketing_content' => '<h3>Own the "Featured Snippets"</h3><p>Questions are how the world searches now. The <b>Authority Q&A Engine</b> identifies exactly what your audience is asking and provides expert-level answers.</p><ul><li><b>Schema Generation:</b> Includes ready-to-paste JSON-LD script to make your FAQs visible on the search page.</li><li><b>Intent-Based Q&A:</b> Generates answers for users at every stage of the buying journey.</li><li><b>E-E-A-T Builder:</b> High-quality answers that prove your expertise to Google\'s "Helpful Content" algorithm.</li></ul>',
            'features' => [
                ['icon' => 'fa-list-check', 'title' => 'Schema Markup Ready', 'desc' => 'Get FAQPage JSON-LD code ready for your website.'],
                ['icon' => 'fa-brain', 'title' => 'Common Query Miner', 'desc' => 'Identifies the most common questions users ask about your topic.'],
                ['icon' => 'fa-pen', 'title' => 'Expert Answers', 'desc' => 'Generates authoritative answers that build trust and E-E-A-T.']
            ],
            'meta_title' => 'Authority Q&A Engine | VidaNexus AI',
            'meta_desc' => 'Generate SEO-optimized FAQs and schema markup effortlessly.',
            'route' => 'dashboard.seo.faq-generator',
            'daily_limit' => 10,
        ],
        [
            'name' => 'Strategic Market Intelligence',
            'tagline' => 'Generate comprehensive market research reports.',
            'icon' => 'fa-chart-simple',
            'slug' => 'market-research',
            'color' => '#6366f1',
            'required_tier' => 'pro',
            'category' => 'intelligence',
            'unlock_price' => 499,
            'credit_cost_per_action' => 3,
            'initial_bonus_credits' => 10,
            'description' => 'Get a deep dive into any niche or industry. Understand market size, demographics, and competitive landscape.',
            'marketing_content' => '<h3>Data-Driven Business Strategy</h3><p>Guessing is expensive. The <b>Strategic Market Intelligence</b> tool provides you with the data you need to make confident decisions.</p><ul><li><b>Demographic Deep-Dives:</b> Understand the age, location, and pain points of your ideal customer.</li><li><b>Competitor Landscape:</b> See who else is in the market and where their weaknesses lie.</li><li><b>Trend Forecasting:</b> Identify if your niche is growing or shrinking before you invest.</li></ul>',
            'features' => [
                ['icon' => 'fa-users-viewfinder', 'title' => 'Audience Insights', 'desc' => 'Detailed breakdown of target demographics and behaviors.'],
                ['icon' => 'fa-magnifying-glass-trending', 'title' => 'Trend Analysis', 'desc' => 'Identify upward and downward movements in your specific market.'],
                ['icon' => 'fa-shield-halved', 'title' => 'Risk & Opportunity', 'desc' => 'A structured look at external factors affecting your business success.']
            ],
            'meta_title' => 'Strategic Market Intelligence | VidaNexus AI',
            'meta_desc' => 'Professional AI-driven market research and industry analysis.',
            'route' => 'dashboard.marketing.market-research',
            'daily_limit' => 3,
        ],
        [
            'name' => 'Topical Map Architect',
            'tagline' => 'Map your topical authority.',
            'icon' => 'fa-map-location-dot',
            'slug' => 'keyword-coverage',
            'color' => '#ec4899',
            'required_tier' => 'pro',
            'category' => 'seo',
            'unlock_price' => 499,
            'credit_cost_per_action' => 3,
            'initial_bonus_credits' => 10,
            'description' => 'Check which topics and keywords you have covered versus what the niche requires for topical authority.',
            'marketing_content' => '<h3>Blueprint for Topical Dominance</h3><p>Google doesn\'t rank singular pages anymore; it ranks AUTHORITIES. The <b>Topical Map Architect</b> shows you exactly what articles you need to write to own a category.</p><ul><li><b>Hierarchy Discovery:</b> See the parent and child topics required to build a perfect content silo.</li><li><b>Content Gap Identification:</b> Instantly see where your website has "Blind Spots" in its coverage.</li><li><b>Authority Scorecard:</b> Track your progress as you fill the gaps and become the go-to resource in your field.</li></ul>',
            'features' => [
                ['icon' => 'fa-layer-group', 'title' => 'Topical Map Generator', 'desc' => 'See the full hierarchy of topics needed to dominate your niche.'],
                ['icon' => 'fa-check-double', 'title' => 'Gap Identifier', 'desc' => 'Instantly see which sub-topics you are missing in your content silo.'],
                ['icon' => 'fa-arrow-up-right-dots', 'title' => 'Authority Score', 'desc' => 'Get a percentage score of how close you are to becoming a niche leader.']
            ],
            'meta_title' => 'Topical Map Architect | VidaNexus AI',
            'meta_desc' => 'Analyze your topical authority and SEO keyword coverage.',
            'route' => 'dashboard.seo.keyword-coverage',
            'daily_limit' => 5,
        ],
        [
            'name' => 'Growth Roadmap Architect',
            'tagline' => 'Your 30-day roadmap to growth.',
            'icon' => 'fa-route',
            'slug' => 'marketing-plan',
            'color' => '#14b8a6',
            'required_tier' => 'pro',
            'category' => 'marketing',
            'unlock_price' => 499,
            'credit_cost_per_action' => 3,
            'initial_bonus_credits' => 10,
            'description' => 'Generate a comprehensive digital marketing strategy tailored to your product and budget.',
            'marketing_content' => '<h3>Your Blueprint for Scaling</h3><p>Stop doing random acts of marketing. The <b>Growth Roadmap Architect</b> provides a structured 30-day plan to move the needle.</p><ul><li><b>Chronological Execution:</b> Know exactly what to do on Day 1, Day 15, and Day 30.</li><li><b>Budget Optimization:</b> Get recommendations on where to spend your next $100 for the highest return.</li><li><b>Multi-Channel Synergy:</b> Orchestrate your Social, Email, and Search efforts to work as one.</li></ul>',
            'features' => [
                ['icon' => 'fa-calendar-days', 'title' => '30-Day Execution Calendar', 'desc' => 'Daily tasks to keep your growth on track.'],
                ['icon' => 'fa-money-bill-transfer', 'title' => 'Budget Allocation', 'desc' => 'Optimal spend across Search, Social, and Content.'],
                ['icon' => 'fa-bullseye', 'title' => 'KPI Definition', 'desc' => 'Know exactly what metrics to track to measure your success.']
            ],
            'meta_title' => 'Growth Roadmap Architect | VidaNexus AI',
            'meta_desc' => 'Get a professional, custom digital marketing strategy in seconds.',
            'route' => 'dashboard.marketing.plan',
            'daily_limit' => 2,
        ],
        [
            'name' => 'Visual Strategy Lab',
            'tagline' => 'High-CTR visual concepts.',
            'icon' => 'fa-lightbulb',
            'slug' => 'ad-creative-ideas',
            'color' => '#8b5cf6',
            'required_tier' => 'beginner',
            'category' => 'marketing',
            'unlock_price' => 99,
            'credit_cost_per_action' => 1,
            'initial_bonus_credits' => 10,
            'description' => 'Stuck on image or video ideas? Get high-converting visual concepts for your next ad campaign.',
            'marketing_content' => '<h3>Concept-to-Creation Mastery</h3><p>Bad creative kills good ads. The <b>Visual Strategy Lab</b> generates the concepts that make people stop and stare.</p><ul><li><b>Visual Hooks:</b> Precise descriptions for images or videos that trigger high emotional responses.</li><li><b>AI-Prompt Ready:</b> Descriptions ready to be pasted into tools like Midjourney or DALL-E for instant production.</li><li><b>Competitor-Proof Styling:</b> Unique visual angles that help you stand out in crowded feeds.</li></ul>',
            'features' => [
                ['icon' => 'fa-image', 'title' => 'Image Concept Board', 'desc' => 'Visual descriptions for designers or AI image generators.'],
                ['icon' => 'fa-video', 'title' => 'Video Hook Scripts', 'desc' => 'The first 3 seconds of your video ad to stop the scroll.'],
                ['icon' => 'fa-face-laugh-beam', 'title' => 'Psychological Triggers', 'desc' => 'Built-in emotional triggers to drive action.']
            ],
            'meta_title' => 'Visual Strategy Lab | VidaNexus AI',
            'meta_desc' => 'Visual and conceptual ideas for high-converting ad campaigns.',
            'route' => 'dashboard.marketing.creative-ideas',
            'daily_limit' => 20,
        ],
        [
            'name' => 'Customer Avatar Architect',
            'tagline' => 'Know your customer deeply.',
            'icon' => 'fa-user-group',
            'slug' => 'buyer-persona',
            'color' => '#f43f5e',
            'required_tier' => 'growth',
            'category' => 'intelligence',
            'unlock_price' => 299,
            'credit_cost_per_action' => 2,
            'initial_bonus_credits' => 10,
            'description' => 'Create detailed avatars of your ideal customers, including pain points and motivations.',
            'marketing_content' => '<h3>Inside the Mind of Your Market</h3><p>If you market to everyone, you market to no one. The <b>Customer Avatar Architect</b> creates a living, breathing profile of your best customer.</p><ul><li><b>Hidden Motivations:</b> Discover the deep-seated "Why" behind their purchasing decisions.</li><li><b>Channel Strategy:</b> Know exactly where they hang out online so you stop wasting budget on the wrong platforms.</li><li><b>Language Matching:</b> Learn the specific vocabulary and tone your customers use so you can speak their language.</li></ul>',
            'features' => [
                ['icon' => 'fa-comments', 'title' => 'Customer Language', 'desc' => 'The exact words and phrases your customers use to describe their problems.'],
                ['icon' => 'fa-circle-exclamation', 'title' => 'Pain Point Analysis', 'desc' => 'Deep dive into the challenges your product solves for each persona.'],
                ['icon' => 'fa-map', 'title' => 'Buying Journey', 'desc' => 'Understand the steps they take from awareness to purchase.']
            ],
            'meta_title' => 'Customer Avatar Architect | VidaNexus AI',
            'meta_desc' => 'Generate detailed customer personas to target your marketing effectively.',
            'route' => 'dashboard.marketing.buyer-persona',
            'daily_limit' => 5,
        ],
        [
            'name' => 'Strategic SWOT Intelligence',
            'tagline' => 'Strategic business intelligence.',
            'icon' => 'fa-rectangle-list',
            'slug' => 'swot-analysis',
            'color' => '#06b6d4',
            'required_tier' => 'growth',
            'category' => 'intelligence',
            'unlock_price' => 299,
            'credit_cost_per_action' => 2,
            'initial_bonus_credits' => 10,
            'description' => 'Professional SWOT reports for any company or project to aid strategic decision making.',
            'marketing_content' => '<h3>The Strategic High Ground</h3><p>Success is about knowing when to attack and when to defend. <b>Strategic SWOT Intelligence</b> provide an unbiased view of your business landscape.</p><ul><li><b>TOWS Matrix Integration:</b> Beyond SWOT, get actionable strategies on how to leverage strengths to solve threats.</li><li><b>External Factor Analysis:</b> Don\'t get blindsided by market shifts or regulatory changes.</li><li><b>Internal Health Check:</b> Identify silent weaknesses before they become critical failures.</li></ul>',
            'features' => [
                ['icon' => 'fa-shield', 'title' => 'Defense Strategy', 'desc' => 'How to mitigate your weaknesses and external threats.'],
                ['icon' => 'fa-sword', 'title' => 'Attack Plan', 'desc' => 'Leverage your strengths to capture new market opportunities.'],
                ['icon' => 'fa-file-pdf', 'title' => 'Presentation Ready', 'desc' => 'Exportable insights for your board or team meetings.']
            ],
            'meta_title' => 'Strategic SWOT Intelligence | VidaNexus AI',
            'meta_desc' => 'Generate professional SWOT analysis reports for your business.',
            'route' => 'dashboard.marketing.swot',
            'daily_limit' => 10,
        ],
        [
            'name' => 'Content Precision Metrics',
            'tagline' => 'Master your content length.',
            'icon' => 'fa-arrow-down-9-1',
            'slug' => 'word-counter',
            'color' => '#10b981',
            'required_tier' => 'beginner',
            'category' => 'tools',
            'unlock_price' => 49,
            'credit_cost_per_action' => 0,
            'initial_bonus_credits' => 10,
            'description' => 'Fast and accurate word count, reading time estimation, and keyword density analyzer.',
            'marketing_content' => '<h3>The Science of Content Length</h3><p>Every platform has a "Golden Ratio" of content length. <b>Content Precision Metrics</b> ensures you never miss the mark.</p><ul><li><b>Density Auditor:</b> Prevent "Keyword Overstuffing" penalties while ensuring maximum topical relevance.</li><li><b>Readability Scoring:</b> Make sure your text is written at the perfect level for your target demographic.</li><li><b>Time-to-Value:</b> Estimated reading time to help you better structure your long-form guides.</li></ul>',
            'features' => [
                ['icon' => 'fa-clock', 'title' => 'Readability Score', 'desc' => 'Ensure your content is accessible to your target audience.'],
                ['icon' => 'fa-magnifying-glass', 'title' => 'Keyword Density', 'desc' => 'Analyze how often you use your target keywords without overstuffing.'],
                ['icon' => 'fa-share-nodes', 'title' => 'Social Snip', 'desc' => 'See how your text fits in Twitter, FB, or LinkedIn limits.']
            ],
            'meta_title' => 'Content Precision Metrics | VidaNexus AI',
            'meta_desc' => 'Analyze word count, reading time, and keyword density.',
            'route' => 'dashboard.seo.word-counter',
            'daily_limit' => 50,
        ],
        [
            'name' => 'Viral Script Architect',
            'tagline' => 'Viral scripts for YouTube & TikTok.',
            'icon' => 'fa-clapperboard',
            'slug' => 'video-script',
            'color' => '#f97316',
            'required_tier' => 'growth',
            'category' => 'content',
            'unlock_price' => 299,
            'credit_cost_per_action' => 2,
            'initial_bonus_credits' => 10,
            'description' => 'Generate high-retention scripts for short-form (TikTok, Reels) and long-form YouTube content.',
            'marketing_content' => '<h3>The Algorithm-Proof Script System</h3><p>Retention is the only metric that matters in video. The <b>Viral Script Architect</b> structures your scripts based on the psychology of attention.</p><ul><li><b>The 3-Second Hook:</b> Specialized opening lines designed to block the scroll on high-velocity platforms.</li><li><b>Visual Pacing:</b> Includes "B-Roll" suggestions and visual transition cues alongside your spoken text.</li><li><b>Emotional Resonance:</b> Leverages storytelling arcs that keep viewers watching until the final call-to-action.</li></ul>',
            'features' => [
                ['icon' => 'fa-hook', 'title' => 'Attention Hooks', 'desc' => 'Proven opening lines to keep viewers from swiping away.'],
                ['icon' => 'fa-scroll', 'title' => 'Full Storyboarding', 'desc' => 'Visual instructions paired with your script for easy filming.'],
                ['icon' => 'fa-music', 'title' => 'Audio Suggestions', 'desc' => 'Recommended pacing and background track styles for maximum impact.']
            ],
            'meta_title' => 'Viral Script Architect | VidaNexus AI',
            'meta_desc' => 'Generate viral scripts for TikTok, Reels, and YouTube using AI.',
            'route' => 'dashboard.marketing.video-script',
            'daily_limit' => 5,
        ],

        [
            'name' => 'AuditX Core Scanner',
            'tagline' => 'Deep technical SEO auditing.',
            'icon' => 'fa-stethoscope',
            'slug' => 'auditx',
            'color' => '#f43f5e',
            'required_tier' => 'pro',
            'category' => 'seo',
            'unlock_price' => 499,
            'credit_cost_per_action' => 3,
            'initial_bonus_credits' => 10,
            'description' => 'Uncover hidden technical errors holding your website back from page one rankings.',
            'marketing_content' => '<h3>Find the Friction Holding You Back</h3><p>Great content is useless if Google can\'t crawl it. <b>AuditX Core Scanner</b> identifies the critical technical flaws in your website architecture.</p><ul><li><b>Core Web Vitals:</b> Check speeds and visual stability scores.</li><li><b>Indexability Checks:</b> Find rogue no-index tags and crawl errors.</li><li><b>Actionable Fixes:</b> Plain-English instructions on how to patch vulnerabilities.</li></ul>',
            'features' => [
                ['icon' => 'fa-bug', 'title' => 'Error Detection', 'desc' => 'Find broken links, missing meta, and redirect chains.'],
                ['icon' => 'fa-gauge-high', 'title' => 'Speed Analysis', 'desc' => 'Identify rendering blocks and heavy assets.'],
                ['icon' => 'fa-file-code', 'title' => 'Technical Report', 'desc' => 'Export fixes directly for your development team.']
            ],
            'meta_title' => 'AuditX Core Scanner | VidaNexus AI',
            'meta_desc' => 'Deep technical SEO and Core Web Vitals auditing.',
            'route' => 'dashboard.auditx.index',
            'daily_limit' => 3,
        ],
        [
            'name' => 'Viral Drama Detector',
            'tagline' => 'Capitalize on trending social dramas.',
            'icon' => 'fa-masks-theater',
            'slug' => 'drama-trends',
            'color' => '#8b5cf6',
            'required_tier' => 'pro',
            'category' => 'intelligence',
            'unlock_price' => 499,
            'credit_cost_per_action' => 3,
            'initial_bonus_credits' => 10,
            'description' => 'Track exploding cultural moments, TV shows, and trending dramas to hijack traffic.',
            'marketing_content' => '<h3>Monetize the Culture</h3><p>Attention flows where the drama goes. <b>Viral Drama Detector</b> shows you exact Google Search velocity on cultural phenomena (like Ramadan series) so you can ride the wave.</p><ul><li><b>Entity Tracking:</b> Follow specific shows, actors, or public figures in real-time.</li><li><b>News-Jacking Ammo:</b> Get instant data to write reaction pieces before the trend dies.</li><li><b>Predictive Scoring:</b> See which series are gaining momentum hour-by-hour.</li></ul>',
            'features' => [
                ['icon' => 'fa-arrow-trend-up', 'title' => 'Real-Time Spikes', 'desc' => 'Track 48+ entities to see what the public is obsessed with today.'],
                ['icon' => 'fa-chart-area', 'title' => 'Historical Comparison', 'desc' => 'Compare the staying power of different trends over 30 days.'],
                ['icon' => 'fa-bolt', 'title' => 'News-Jacking', 'desc' => 'Find the perfect angle to inject your brand into the conversation.']
            ],
            'meta_title' => 'Viral Drama Detector | VidaNexus AI',
            'meta_desc' => 'Track viral entertainment and cultural trends for massive traffic.',
            'route' => 'dashboard.drama-trends.index',
            'daily_limit' => 10,
        ],
        [
            'name' => 'Image-to-Text Synthesizer',
            'tagline' => 'Extract content from any image.',
            'icon' => 'fa-file-image',
            'slug' => 'folio-ocr',
            'color' => '#3b82f6',
            'required_tier' => 'beginner',
            'category' => 'tools',
            'unlock_price' => 49,
            'credit_cost_per_action' => 1,
            'initial_bonus_credits' => 10,
            'description' => 'Instantly convert images, screenshots, and PDFs into editable, SEO-friendly text.',
            'marketing_content' => '<h3>Liberate Your Data</h3><p>Don\'t let valuable information stay locked in pixels. The <b>Image-to-Text Synthesizer</b> uses advanced OCR to pull perfectly formatted text from any visual format.</p><ul><li><b>Multi-Lingual OCR:</b> Perfect recognition for both English and Arabic texts.</li><li><b>Table Extraction:</b> Keep your structural formatting intact when scanning documents.</li><li><b>Instant Repurposing:</b> Turn infographics into blog posts in seconds.</li></ul>',
            'features' => [
                ['icon' => 'fa-language', 'title' => 'Bilingual Accuracy', 'desc' => 'Flawless Arabic and English character recognition.'],
                ['icon' => 'fa-table', 'title' => 'Format Preservation', 'desc' => 'Maintains paragraphs and lists exactly as seen.'],
                ['icon' => 'fa-bolt', 'title' => 'High Speed', 'desc' => 'Process heavy documents in a fraction of the time.']
            ],
            'meta_title' => 'Image-to-Text Synthesizer | VidaNexus AI',
            'meta_desc' => 'Advanced OCR tool to convert images to editable text.',
            'route' => 'dashboard.folio-ocr.index',
            'daily_limit' => 20,
        ],
        [
            'name' => 'Speed Optimization Engine',
            'tagline' => 'Crush load times, boost rankings.',
            'icon' => 'fa-compress',
            'slug' => 'img-compress',
            'color' => '#10b981',
            'required_tier' => 'beginner',
            'category' => 'tools',
            'unlock_price' => 49,
            'credit_cost_per_action' => 0,
            'initial_bonus_credits' => 10,
            'description' => 'Lossless image compression to ensure your site loads instantly and passes Core Web Vitals.',
            'marketing_content' => '<h3>The Need for Speed</h3><p>A one-second delay in page load equals a 7% loss in conversions. The <b>Speed Optimization Engine</b> visually shrinks your images while retaining 100% of their quality.</p><ul><li><b>WebP Conversion:</b> Automatically converts heavy JPG/PNG to next-gen WebP formats.</li><li><b>Bulk Processing:</b> Optimize entire galleries in one batch.</li><li><b>SEO Ready:</b> Keeps file names intact and strips heavy EXIF data.</li></ul>',
            'features' => [
                ['icon' => 'fa-down-left-and-up-right-to-center', 'title' => 'Massive Reduction', 'desc' => 'Shrink file sizes by up to 80% without visual loss.'],
                ['icon' => 'fa-file-export', 'title' => 'Next-Gen Formats', 'desc' => 'Output to WebP for maximum Google compliance.'],
                ['icon' => 'fa-gauge-high', 'title' => 'Speed Boost', 'desc' => 'Directly improve your Google PageSpeed Insights score.']
            ],
            'meta_title' => 'Speed Optimization Engine | VidaNexus AI',
            'meta_desc' => 'Compress images without quality loss for faster websites.',
            'route' => 'dashboard.img-compress.index',
            'daily_limit' => 50,
        ],
        [
            'name' => 'Automated Revenue Content',
            'tagline' => 'Set your content generation on autopilot.',
            'icon' => 'fa-money-bill-wave',
            'slug' => 'money-printer',
            'color' => '#f59e0b',
            'required_tier' => 'ultimate',
            'category' => 'content',
            'unlock_price' => 999,
            'credit_cost_per_action' => 5,
            'initial_bonus_credits' => 10,
            'description' => 'Batch generate hundreds of optimized articles and schedule them directly to your CMS.',
            'marketing_content' => '<h3>Scale Without Limits</h3><p>The <b>Automated Revenue Content</b> system is for publishers who want total domination. Feed it a list of keywords and watch it build an entire website\'s worth of content while you sleep.</p><ul><li><b>Bulk Execution:</b> Generate 50-100 articles from a single CSV upload.</li><li><b>Internal Linking:</b> Automatically connects posts together to build massive topical authority silos.</li><li><b>CMS Integration:</b> pushes directly into WordPress as drafts or scheduled posts.</li></ul>',
            'features' => [
                ['icon' => 'fa-cubes', 'title' => 'Batch Generation', 'desc' => 'Produce an entire month of content in one sitting.'],
                ['icon' => 'fa-link', 'title' => 'Silo Architect', 'desc' => 'Automatically builds intelligent internal links.'],
                ['icon' => 'fa-wordpress', 'title' => 'Direct Publish', 'desc' => 'Skip the copy-paste and push straight to your site.']
            ],
            'meta_title' => 'Automated Revenue Content | VidaNexus AI',
            'meta_desc' => 'Bulk AI article generation and WordPress scheduling.',
            'route' => 'dashboard.money-printer.index',
            'daily_limit' => 1,
        ],
        [
            'name' => 'Technical SEO Auditor',
            'tagline' => 'The ultimate on-page checklist.',
            'icon' => 'fa-clipboard-check',
            'slug' => 'seo-auditor',
            'color' => '#ec4899',
            'required_tier' => 'growth',
            'category' => 'tools',
            'unlock_price' => 299,
            'credit_cost_per_action' => 2,
            'initial_bonus_credits' => 10,
            'description' => 'Scan any URL to get a comprehensive breakdown of its On-Page SEO health.',
            'marketing_content' => '<h3>Perfection on Every Page</h3><p>Don\'t guess if your post is optimized. The <b>Technical SEO Auditor</b> acts as your personal editor before you hit publish.</p><ul><li><b>Keyword Placement:</b> Verifies exact match presence in Title, URL, H1, and Meta.</li><li><b>Content Structure:</b> Checks header depth, image alt tags, and outbound link counts.</li><li><b>Competitor Baseline:</b> See how your on-page score compares to the #1 ranking result.</li></ul>',
            'features' => [
                ['icon' => 'fa-check', 'title' => 'Live Scoring', 'desc' => 'Get a grade from 1-100 on your immediate SEO health.'],
                ['icon' => 'fa-list-ol', 'title' => 'Action Item List', 'desc' => 'A clear checklist of exactly what to change to score 100.'],
                ['icon' => 'fa-tags', 'title' => 'Meta Analysis', 'desc' => 'Checks length, density, and duplication of all tags.']
            ],
            'meta_title' => 'Technical SEO Auditor | VidaNexus AI',
            'meta_desc' => 'Comprehensive On-Page SEO analysis and scoring.',
            'route' => 'dashboard.seo-auditor.index',
            'daily_limit' => 10,
        ],
        [
            'name' => 'Instant App Converter',
            'tagline' => 'Turn your website into a mobile app.',
            'icon' => 'fa-mobile-button',
            'slug' => 'web-to-app',
            'color' => '#6366f1',
            'required_tier' => 'agency',
            'category' => 'tools',
            'unlock_price' => 1499,
            'credit_cost_per_action' => 5,
            'initial_bonus_credits' => 10,
            'description' => 'Wrap your mobile-optimized website into a native Android/iOS shell ready for App Store submission.',
            'marketing_content' => '<h3>Own Their Home Screen</h3><p>Websites wait for traffic; Apps live in your customer\'s pocket. The <b>Instant App Converter</b> transforms your existing web presence into a high-value native application.</p><ul><li><b>Push Notifications:</b> Bypass the inbox and send alerts straight to their phone.</li><li><b>Zero Coding Required:</b> Generates the APK/AAB files autonomously.</li><li><b>Brand Elevation:</b> Being in the App Store instantly multiplies your brand\'s perceived value.</li></ul>',
            'features' => [
                ['icon' => 'fa-android', 'title' => 'Native Wrapping', 'desc' => 'Creates clean, fast WebView shells for your site.'],
                ['icon' => 'fa-bell', 'title' => 'Push Ready', 'desc' => 'Integrates with Firebase for instant push notifications.'],
                ['icon' => 'fa-rocket', 'title' => 'Launch Ready', 'desc' => 'Files delivered ready to upload to Google Play or Apple Store.']
            ],
            'meta_title' => 'Instant App Converter | VidaNexus AI',
            'meta_desc' => 'Convert any website into a native mobile app instantly.',
            'route' => 'dashboard.web-to-app.index',
            'daily_limit' => 1,
        ],
    ],
    
    'subscription_plans' => [],
];
