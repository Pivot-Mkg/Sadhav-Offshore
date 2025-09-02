console.log('jobs-handler.js loaded');

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded, initializing job listings');
    // Function to create job cards
    function createJobCard(job) {
        return `
            <div class="col-lg-6">
                <div class="job-card card h-100 shadow-sm rounded overflow-hidden">
                    <div class="card-body d-flex flex-column">
                        <h3 class="h4 mb-2">${job.title}</h3>
                        <div class="text-muted mb-3">
                            <i class="fas fa-map-marker-alt me-1"></i> ${job.location}
                        </div>
                        <p class="mb-3">${job.description.substring(0, 150)}...</p>
                        <div class="d-flex gap-2 flex-wrap mb-3">
                            ${job.tags.map(tag => `<span class="badge text-dark border">${tag}</span>`).join('')}
                        </div>
                        <div class="job-actions mt-auto">
                            <a data-bs-toggle="modal" data-bs-target="#${job.id}Modal" href="#" class="card__link">Learn More</a>
                            <a href="#application-form" class="button button--primary button--lg apply-now" data-job-title="${job.title.replace(/"/g, '&quot;')}">Apply Now</a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Function to create modal for each job
    function createJobModal(job) {
        return `
            <div class="modal fade" id="${job.id}Modal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${job.title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="job-detail-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <strong>Location:</strong> ${job.location}
                            </div>
                            <div class="job-detail-item">
                                <i class="fas fa-briefcase"></i>
                                <strong>Experience:</strong> ${job.experience}
                            </div>
                            <div class="job-detail-item">
                                <i class="fas fa-calendar-alt"></i>
                                <strong>Job Type:</strong> ${job.type}
                            </div>
                            
                            <h6 class="mt-4">Job Description</h6>
                            <p>${job.description}</p>
                            
                            <h6>Key Responsibilities</h6>
                            <ul>
                                ${job.responsibilities.map(resp => `<li>${resp}</li>`).join('')}
                            </ul>
                            
                            <h6>Requirements</h6>
                            <ul>
                                ${job.requirements.map(req => `<li>${req}</li>`).join('')}
                            </ul>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <a href="#application-form" class="button button--primary button--lg apply-now" data-job-title="${job.title.replace(/"/g, '&quot;')}">Apply Now</a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Function to initialize the page
    function initializePage() {
        const jobsContainer = document.getElementById('jobs-container');
        const modalsContainer = document.getElementById('modals-container');
        
        if (!jobsContainer || !modalsContainer) return;
        
        // Clear existing content
        jobsContainer.innerHTML = '';
        modalsContainer.innerHTML = '';
        
        // Create job cards and modals
        try {
            if (!Array.isArray(jobsData)) {
                throw new Error('jobsData is not an array');
            }
            
            console.log(`Creating ${jobsData.length} job cards and modals`);
            
            jobsData.forEach((job, index) => {
                try {
                    if (!job || !job.id) {
                        console.error('Invalid job data at index', index, job);
                        return;
                    }
                    
                    const jobCard = createJobCard(job);
                    const jobModal = createJobModal(job);
                    
                    if (jobCard) jobsContainer.insertAdjacentHTML('beforeend', jobCard);
                    if (jobModal) modalsContainer.insertAdjacentHTML('beforeend', jobModal);
                    
                } catch (error) {
                    console.error(`Error processing job at index ${index}:`, error);
                }
            });
            
            console.log(`Created ${jobsContainer.children.length} job cards and ${modalsContainer.children.length} modals`);
            
        } catch (error) {
            console.error('Error creating job listings:', error);
            jobsContainer.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-warning">
                        <h4>Unable to load job listings</h4>
                        <p>Please try refreshing the page or contact support if the problem persists.</p>
                        <p class="small text-muted mb-0">Error: ${error.message}</p>
                    </div>
                </div>`;
        }
    }

    // Function to handle Apply Now button clicks
    function handleApplyNowClick(e) {
        e.preventDefault();
        const jobTitle = this.getAttribute('data-job-title');
        const positionSelect = document.getElementById('position');
        
        // Close any open modal
        const modal = bootstrap.Modal.getInstance(document.querySelector('.modal.show'));
        if (modal) {
            modal.hide();
        }
        
        if (positionSelect) {
            // Find and select the option that contains the job title
            for (let i = 0; i < positionSelect.options.length; i++) {
                if (positionSelect.options[i].text.toLowerCase().includes(jobTitle.toLowerCase())) {
                    positionSelect.selectedIndex = i;
                    break;
                }
            }
            
            // Add smooth scrolling to the form
            const formSection = document.getElementById('application-form');
            if (formSection) {
                // Small delay to allow modal to close before scrolling
                setTimeout(() => {
                    formSection.scrollIntoView({ behavior: 'smooth' });
                    // Focus on the first form field for better UX
                    const firstNameField = document.getElementById('firstName');
                    if (firstNameField) {
                        firstNameField.focus();
                    }
                }, 300); // 300ms delay to allow modal to close
            }
        }
    }

    // Add event delegation for Apply Now buttons
    document.addEventListener('click', function(e) {
        if (e.target.matches('.apply-now') || e.target.closest('.apply-now')) {
            const button = e.target.matches('.apply-now') ? e.target : e.target.closest('.apply-now');
            handleApplyNowClick.call(button, e);
        }
    });

    // Initialize the page
    console.log('Initializing page...');
    try {
        console.log('Calling initializePage()');
        initializePage();
        console.log('Page initialization complete');
        
        // Debug: Check if elements were created
        setTimeout(() => {
            const jobCards = document.querySelectorAll('.job-card');
            console.log(`Found ${jobCards.length} job cards in the DOM`);
            
            // Check if modals were created
            const modals = document.querySelectorAll('.modal');
            console.log(`Found ${modals.length} modals in the DOM`);
            
            // Check if jobsData is accessible
            console.log('jobsData in global scope:', window.jobsData);
        }, 1000);
    } catch (error) {
        console.error('Error initializing page:', error);
    }
});
