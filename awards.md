---
title: Leadership Awards
layout: page

---
Each year, Unami Lodge presents various awards at the annual banquet. Recipients are nominated by fellow Arrowman throughout the year.  Below you will find links to all the nomination forms for the current year.

<table class="table table-striped mt-3">
	<thead>
		<tr>
			<th scope="col">Award</th>
			<th scope="col">Deadline</th>
			<th scope="col">Link</th>
		</tr>
	</thead>
	<tbody>
		{% for award in site.data.awards %}
			<tr>
				<td class="align-middle">{{award.title}}</td>
				<td class="align-middle">{{award.deadline}}</td>
				{% if award.link-url %}
					<td class="align-middle"><a class="btn btn-primary" href="{{award.link-url}}">{{award.link-text}}</a></td>
				{% else %}
					<td class="align-middle">{{award.link-text}}</td>
				{% endif %}
			</tr>
		{% endfor %}
	</tbody>
</table>