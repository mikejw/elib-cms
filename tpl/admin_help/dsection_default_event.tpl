<p>Welcome to ELib CMS.</p>

<h2>Overview</h2>
<p>
	ELib CMS is designed to be both simple and powerful.
	This is achieved by the system only being aware of two types of
	general information. Sections and data. The third hidden
	component are the templates themselves, which take 
	the form of regular controllers - located within
	an application module that can be specified 
	in the config.yml file 'dynamic_module' setting.
</p>

<p>
	Most importantly, sections and their automatically 'slugged' uri components, 
	correspond exactly to URLs within your application and can be nested as much as desired.
</p>

<p>
	Any section can use a template. These can be changed by clicking on a 
	section and then on Change Template.  These templates are internal and 
	will dictate exactly how each dynamic section will look, with a controller
	that is responsible for fetching the section data, together with a
	coresponding Smarty view template.
</p> 

<p>
	Perhaps confusingly data items can be nested within further data
	items but this is crucial to allow for complex templates.  Apart from
	containers, data items correspond to any chunk of information that has
	been uploaded to the site such as a piece of text, an image or a video.
	Data items can also have secondary text associated with it by using the 'Meta' button.
	This can be useful for giving an image a title as an example.
</p>

