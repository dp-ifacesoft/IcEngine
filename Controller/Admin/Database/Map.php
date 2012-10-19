<?php

class Controller_Admin_Database_Map extends Controller_Abstract
{
	public function index ()
	{
		$user = User::getCurrent ();

		if (
			!$user->hasRole ('admin') &&
			!$user->hasRole ('content-manager')
		)
		{
			return $this->replaceAction (
				'Error',
				'accessDenied'
			);
		}

		list (
			$row_id,
			$table
		) = $this->_input->receive (
			'row_id',
			'table'
		);

		$class_name = Model_Scheme::tableToModel ($table);

		$row = Model_Manager::byKey (
			$class_name,
			$row_id
		);

		if (!$row)
		{
			return;
		/*	return $this->replaceAction (
				'Error',
				'notFound'
			); */
		}


		$geo_points = $row->component ('Geo_Point');

		$geo_polylines = $row->component ('Geo_Polyline');

		$geo_polygons = $row->component ('Geo_Polygon');

		$styles = array ();

		$tmp = Model_Collection_Manager::create ($geo_polylines->className ())
			->assign ($geo_polylines)
			->add ($geo_polygons);

		foreach ($tmp as $item)
		{
			$styles [] = array (
				'name'	=> $item->Geo_Line_Style->name,
				'data'	=> $item->Geo_Line_Style->data,
			);
		}

		$style_collection = Model_Collection_Manager::create (
			'Geo_Point_Style'
		);

		$lat = 0;
		$long = 0;

		$tmp = Model_Collection_Manager::create ($geo_polylines->className ())
			->reset ();

		if ($geo_polylines->first ())
		{
			$tmp->add ($geo_polylines->first ()->points ());
		}

		if ($geo_polygons->first ())
		{
			$tmp->add ($geo_polygons->first ()->points ());
		}

		$tmp->add ($geo_points);

		$first_point = $tmp->first ();

		if ($first_point)
		{
			$lat = $first_point->latitude;
			$long = $first_point->longitude;
		}

		$this->_output->send (array (
			'geo_points'		=> $geo_points,
			'geo_polylines'		=> $geo_polylines,
			'geo_polygons'		=> $geo_polygons,
			'styles'			=> $styles,
			'style_collection'	=> $style_collection,
			'table'				=> $table,
			'row_id'			=> $row_id,
			'lat'				=> $lat,
			'long'				=> $long
		));
	}

	public function save ()
	{
		$this->_task->setTemplate (null);

		list (
			$table,
			$row_id,
			$data
		) = $this->_input->receive (
			'table',
			'row_id',
			'data'
		);

		$user = User::getCurrent ();

		if (
			!$user->hasRole ('admin') &&
			!$user->hasRole ('content-manager')
		)
		{
			return;
		}

		list (
			$row_id,
			$table
		) = $this->_input->receive (
			'row_id',
			'table'
		);

		$class_name = Model_Scheme::tableToModel ($table);

		$row = Model_Manager::byKey (
			$class_name,
			$row_id
		);

		if (!$row)
		{
			return;
		}

		$row->component ('Geo_Point')->delete ();

		$polylines = $row->component ('Geo_Polyline');

		foreach ($polylines as $polyline)
		{
			$polyline->component ('Geo_Point')->delete ();
		}

		$polylines->delete ();

		$polygons = $row->component ('Geo_Polygon');

		foreach ($polygons as $polygon)
		{
			$polygon->component ('Geo_Point')->delete ();
		}

		$polygons->delete ();

		foreach ($data as $point)
		{
			$cname = 'Point';

			if ($point ['type'] != 'Placemark')
			{
				$cname = $point ['type'];

				$style = new Geo_Line_Style (array (
					'name'		=> $point ['style'],
					'data'		=> json_encode ($point ['serializedStyle'])
				));

				$style->save ();
			}
			else
			{
				$style = Model_Manager::byQuery (
					'Geo_Point_Style',
					Query::instance ()
						->where ('name', $point ['style'])
				);
			}

			if (!$style)
			{
				continue;
			}

			$cname = 'Component_Geo_' . $cname;

			$model = new $cname (array (
				'name'						=> $point ['name'],
				'description'				=> $point ['description'],
				'styleData'					=> '',
				'table'						=> $class_name,
				'rowId'						=> $row_id,
				'Geo_Point_Style__id'		=> $style->key (),
				'longitude'					=> 0,
				'latitude'					=> 0
			));

			if ($point ['type'] == 'Placemark')
			{
				$model->set ('hasParent', 0);
				$model->set ('longitude', $point ['points']['lng']);
				$model->set ('latitude', $point ['points']['lat']);

				$model->save ();
			}
			else
			{
				if (empty ($point ['points']))
				{
					continue;
				}

				$model->longitude = $point ['points'][0]['lng'];
				$model->latitude = $point ['points'][0]['lat'];

				$model->save ();

				foreach ($point ['points'] as $p)
				{
					$p = new Component_Geo_Point (array (
						'table'			=> $model->className (),
						'rowId'			=> $model->key (),
						'hasParent'		=> 1,
						'latitude'		=> $p ['lat'],
						'longitude'		=> $p ['lng']
					));

					$p->save ();
				}
			}

			$this->_output->send (array (
				'data'	=> array (
					'success'	=> 1
				)
			));

		}

	}
}
