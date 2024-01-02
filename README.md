Splynx Jcc Add-on
====================

Code for portal:
```
<div class="row">
     <div class="col-lg-5">
		<div class="panel panel-default">
			<div class="panel-heading">Add money by Jcc, {{ customer.name}}!</div>
			<div class="panel-body">
				<br>
				<form class="form-inline" action="/Jcc/" method="post">
					<input class="input-sm form-control" name="amount" style="width: 200px" type="number" placeholder="Amount">
					<button type="submit" class="btn btn-primary">Add</button>
				</form>
			</div>
		</div>
	</div>
</div>

```

Link for pay Invoice:
```
/Jcc/pay-invoice
```


Link for pay Request:
```
/Jcc/pay-request
```